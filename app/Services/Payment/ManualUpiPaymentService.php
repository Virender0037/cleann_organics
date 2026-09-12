<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * All state transitions for a Manual UPI Payment go through here, mirroring
 * RazorpayPaymentService's idempotency rules: once a payment is 'paid',
 * nothing here downgrades or overwrites it again, and every write is
 * guarded inside a locked transaction.
 *
 * Proof screenshots are stored on the private 'local' disk (never 'public')
 * under a server-generated filename — the customer's original filename and
 * extension are never trusted or persisted.
 */
class ManualUpiPaymentService
{
    private const PROOF_DISK = 'local';

    private const PROOF_DIRECTORY = 'manual-upi-proofs';

    /** Only these detected (server-side, not client-supplied) MIME types are ever accepted or stored. */
    private const ALLOWED_MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    /**
     * @return array{available: bool, upiId: ?string, payeeName: string, amount: float, qrSvg: ?string}
     */
    public function buildPaymentDetails(Order $order): array
    {
        $upiId = trim((string) (Setting::cached('payment')['upi_id'] ?? ''));
        $payeeName = (string) config('app.name');

        if ($upiId === '') {
            return [
                'available' => false,
                'upiId' => null,
                'payeeName' => $payeeName,
                'amount' => (float) $order->grand_total,
                'qrSvg' => null,
            ];
        }

        $amount = (float) $order->grand_total;

        $upiUri = 'upi://pay?'.http_build_query([
            'pa' => $upiId,
            'pn' => $payeeName,
            'am' => number_format($amount, 2, '.', ''),
            'cu' => 'INR',
            'tn' => 'Order '.$order->order_number,
        ], '', '&', PHP_QUERY_RFC3986);

        $qrSvg = (new Builder(
            writer: new SvgWriter(),
            data: $upiUri,
            size: 260,
            margin: 10,
        ))->build()->getString();

        return [
            'available' => true,
            'upiId' => $upiId,
            'payeeName' => $payeeName,
            'amount' => $amount,
            'qrSvg' => $qrSvg,
        ];
    }

    /**
     * Records (or re-records, on resubmission after a rejection) the
     * customer's proof of payment. Never touches an already-'paid' payment.
     * A newly uploaded screenshot replaces and deletes the previous one, if
     * any, so a resubmission never leaves an orphaned file behind; if no new
     * screenshot is given, whatever proof file already exists is left alone.
     */
    public function submitProof(Order $order, string $upiReference, ?UploadedFile $screenshot): void
    {
        DB::transaction(function () use ($order, $upiReference, $screenshot) {
            /** @var Payment $payment */
            $payment = $order->payment()->lockForUpdate()->firstOrFail();

            if ($payment->status === 'paid') {
                return;
            }

            $update = [
                'upi_reference' => $upiReference,
                'submitted_at' => now(),
                'status' => 'pending',
                'rejected_at' => null,
            ];

            if ($screenshot) {
                $oldPath = $payment->proof_path;

                $update['proof_path'] = $this->storeProof($screenshot);

                if ($oldPath) {
                    Storage::disk(self::PROOF_DISK)->delete($oldPath);
                }
            }

            $payment->update($update);
        });

        Log::info('manual_upi.proof_submitted', ['order_id' => $order->id]);
    }

    /**
     * Admin action — never called automatically. Marking paid also confirms
     * the order, same as a captured Razorpay payment.
     */
    public function verify(Payment $payment, ?string $adminNote): void
    {
        DB::transaction(function () use ($payment, $adminNote) {
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($payment->status === 'paid') {
                return;
            }

            $payment->update([
                'status' => 'paid',
                'transaction_id' => $payment->upi_reference,
                'paid_at' => now(),
                'admin_note' => $adminNote,
            ]);

            $order = $payment->order;
            $order->forceFill(['payment_status' => 'paid'])->save();

            if ($order->order_status === 'pending') {
                $order->forceFill([
                    'order_status' => 'confirmed',
                    'confirmed_at' => now(),
                ])->save();
            }
        });

        Log::info('manual_upi.payment_verified', ['payment_id' => $payment->id]);
    }

    /**
     * Admin action — leaves order.payment_status at 'pending' (not
     * 'failed'): the order isn't dead, the customer can resubmit. The more
     * specific 'rejected' state lives only on the Payment row.
     */
    public function reject(Payment $payment, string $adminNote): void
    {
        DB::transaction(function () use ($payment, $adminNote) {
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($payment->status === 'paid') {
                return;
            }

            $payment->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'admin_note' => $adminNote,
            ]);
        });

        Log::info('manual_upi.payment_rejected', ['payment_id' => $payment->id]);
    }

    /**
     * Streams the proof file directly from the private disk — callers are
     * responsible for authorizing the viewer (admin, or the order's owner)
     * before calling this; it never returns a public, shareable URL.
     */
    public function streamProof(Payment $payment): StreamedResponse
    {
        if (! $payment->proof_path || ! Storage::disk(self::PROOF_DISK)->exists($payment->proof_path)) {
            abort(404);
        }

        return Storage::disk(self::PROOF_DISK)->response($payment->proof_path);
    }

    private function storeProof(UploadedFile $file): string
    {
        $extension = self::ALLOWED_MIME_EXTENSIONS[$file->getMimeType()] ?? null;

        if (! $extension) {
            throw new RuntimeException('Unsupported payment proof file type.');
        }

        $filename = Str::uuid()->toString().'.'.$extension;

        return $file->storeAs(self::PROOF_DIRECTORY, $filename, self::PROOF_DISK);
    }
}

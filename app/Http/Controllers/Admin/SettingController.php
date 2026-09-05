<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateEmailSettingsRequest;
use App\Http\Requests\Admin\UpdateGeneralSettingsRequest;
use App\Http\Requests\Admin\UpdatePaymentSettingsRequest;
use App\Http\Requests\Admin\UpdateSeoSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    private const EMAIL_SECRET_KEYS = ['smtp_password'];

    private const PAYMENT_SECRET_KEYS = ['razorpay_secret_key', 'stripe_secret_key'];

    public function general(): View
    {
        $settings = Setting::group('general');

        return view('admin.settings.general.index', compact('settings'));
    }

    public function updateGeneral(UpdateGeneralSettingsRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['logo', 'favicon']);

        foreach (['logo', 'favicon'] as $field) {
            if ($request->hasFile($field)) {
                $existing = Setting::group('general')[$field] ?? null;
                if ($existing) {
                    Storage::disk('public')->delete($existing);
                }

                $data[$field] = $request->file($field)->store('settings', 'public');
            }
        }

        Setting::setMany('general', $data);
        Setting::forget('general');

        return back()->with('success', 'General settings updated.');
    }

    public function seo(): View
    {
        $settings = Setting::group('seo');

        return view('admin.settings.seo.index', compact('settings'));
    }

    public function updateSeo(UpdateSeoSettingsRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('og_image');

        if ($request->hasFile('og_image')) {
            $existing = Setting::group('seo')['og_image'] ?? null;
            if ($existing) {
                Storage::disk('public')->delete($existing);
            }

            $data['og_image'] = $request->file('og_image')->store('settings', 'public');
        }

        Setting::setMany('seo', $data);
        Setting::forget('seo');

        return back()->with('success', 'SEO settings updated.');
    }

    public function email(): View
    {
        $settings = Setting::group('email');

        return view('admin.settings.email.index', compact('settings'));
    }

    public function updateEmail(UpdateEmailSettingsRequest $request): RedirectResponse
    {
        $data = $this->encryptSecrets($request->safe()->all(), self::EMAIL_SECRET_KEYS);

        Setting::setMany('email', $data);

        return back()->with('success', 'Email settings updated.');
    }

    public function payment(): View
    {
        $settings = Setting::group('payment');

        return view('admin.settings.payment.index', compact('settings'));
    }

    public function updatePayment(UpdatePaymentSettingsRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(self::PAYMENT_SECRET_KEYS);
        $data['enable_razorpay'] = $request->boolean('enable_razorpay') ? '1' : '0';
        $data['enable_stripe'] = $request->boolean('enable_stripe') ? '1' : '0';
        $data['enable_cod'] = $request->boolean('enable_cod') ? '1' : '0';
        $data['enable_upi'] = $request->boolean('enable_upi') ? '1' : '0';

        $data = $this->encryptSecrets(array_merge($data, $request->safe()->only(self::PAYMENT_SECRET_KEYS)), self::PAYMENT_SECRET_KEYS);

        Setting::setMany('payment', $data);

        return back()->with('success', 'Payment settings updated.');
    }

    /**
     * Encrypt non-empty secret fields; drop empty ones so they don't overwrite an already-stored value.
     */
    private function encryptSecrets(array $data, array $secretKeys): array
    {
        foreach ($secretKeys as $key) {
            if (! array_key_exists($key, $data)) {
                continue;
            }

            if (filled($data[$key])) {
                $data[$key] = Crypt::encryptString($data[$key]);
            } else {
                unset($data[$key]);
            }
        }

        return $data;
    }
}

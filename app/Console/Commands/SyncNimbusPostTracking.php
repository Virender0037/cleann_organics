<?php

namespace App\Console\Commands;

use App\Models\Shipment;
use App\Services\Shipping\FulfilmentService;
use App\Services\Shipping\NimbusPostService;
use Illuminate\Console\Command;

/**
 * Pulls NimbusPost tracking for in-flight shipments (NimbusPost documents no webhook, so status is polled).
 * Optional: scheduled hourly in routes/console.php, which only runs if the server has the Laravel scheduler cron.
 * Admins can always refresh one order by hand from the order page.
 */
class SyncNimbusPostTracking extends Command
{
    protected $signature = 'nimbuspost:sync-tracking {--limit=50 : Maximum shipments per run}';

    protected $description = 'Refresh NimbusPost tracking for shipments that are still in transit';

    public function handle(FulfilmentService $fulfilment, NimbusPostService $nimbus): int
    {
        if (! $nimbus->isConfigured()) {
            $this->info('NimbusPost is not configured; nothing to sync.');

            return self::SUCCESS;
        }

        $shipments = Shipment::query()
            ->whereIn('status', Shipment::TRACKABLE)
            ->whereNotNull('awb_number')
            ->where(fn ($q) => $q->whereNull('last_synced_at')->orWhere('last_synced_at', '<', now()->subMinutes(30)))
            ->orderBy('last_synced_at')
            ->limit(max(1, (int) $this->option('limit')))
            ->get();

        $synced = 0;
        foreach ($shipments as $shipment) {
            try {
                $fulfilment->refreshTracking($shipment);
                $synced++;
            } catch (\Throwable $e) {
                // One failure (timeout, unknown AWB…) never stops the rest; the next run retries.
                $this->warn("Shipment {$shipment->id}: ".class_basename($e));
            }
        }

        $this->info("Synced {$synced} of {$shipments->count()} shipment(s).");

        return self::SUCCESS;
    }
}

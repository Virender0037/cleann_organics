<?php

namespace App\Console\Commands;

use App\Services\Shipping\Exceptions\NimbusPostException;
use App\Services\Shipping\NimbusPostService;
use Illuminate\Console\Command;

/**
 * Read-only NimbusPost health check for deployments. Prints only whether each setting is present (never a value),
 * then makes one read-only call — GET /v2/warehouses — which proves the API-key pair authenticates and lists the
 * warehouse ids so NIMBUSPOST_WAREHOUSE_ID can be checked. Creates nothing.
 */
class CheckNimbusPost extends Command
{
    protected $signature = 'nimbuspost:check';

    protected $description = 'Verify NimbusPost configuration and authentication (read-only; prints no secrets)';

    public function handle(NimbusPostService $nimbus): int
    {
        $this->line('NIMBUSPOST_ENABLED: '.($nimbus->isEnabled() ? 'true' : 'false'));
        $this->line('Base URL: '.config('nimbuspost.base_url'));

        foreach (['NIMBUSPOST_API_KEY' => 'api_key', 'NIMBUSPOST_API_SECRET' => 'api_secret', 'NIMBUSPOST_WAREHOUSE_ID' => 'warehouse_id', 'NIMBUSPOST_PICKUP_PINCODE' => 'pickup_pincode'] as $name => $key) {
            $this->line(str_pad($name, 28).(filled(config('nimbuspost.'.$key)) ? 'set' : 'MISSING'));
        }

        $apiKey = (string) config('nimbuspost.api_key');
        if ($apiKey !== '' && ! str_starts_with($apiKey, 'npk_')) {
            $this->warn('NIMBUSPOST_API_KEY does not start with "npk_" — v2 key ids always do. Check you pasted the key id, not the secret.');
        }

        if (! $nimbus->isConfigured()) {
            $this->error('Not configured: '.implode(', ', $nimbus->isEnabled() ? $nimbus->missingConfiguration() : ['NIMBUSPOST_ENABLED=true']));

            return self::FAILURE;
        }

        try {
            $warehouses = $nimbus->warehouses();
        } catch (NimbusPostException $e) {
            $this->error('NimbusPost check failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Authentication OK (GET /v2/warehouses succeeded).');

        $configured = (string) config('nimbuspost.warehouse_id');
        $found = false;
        foreach ($warehouses as $warehouse) {
            $id = (string) ($warehouse['warehouse_id'] ?? '');
            $found = $found || $id === $configured;
            $this->line(sprintf('  %s %s — %s (%s %s)', $id === $configured ? '*' : ' ', $id, $warehouse['name'] ?? '', $warehouse['address']['city'] ?? '', $warehouse['address']['pincode'] ?? ''));
        }

        if (! $found) {
            $this->warn('NIMBUSPOST_WAREHOUSE_ID does not match any warehouse listed above.');

            return self::FAILURE;
        }

        $this->info('NIMBUSPOST_WAREHOUSE_ID matches a NimbusPost warehouse (*).');

        return self::SUCCESS;
    }
}

<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceClick;
use App\Models\Product;
use App\Models\ProductMarketplacePrice;
use App\Support\Marketplaces;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * GET /out/marketplace/{marketplacePrice}?src=…
 *
 * The only way customers reach a marketplace. It never reads a destination from the request: it looks the record
 * up by id, requires it to be active, priced, on an enabled marketplace and attached to a publicly visible product,
 * and redirects to the URL stored on that record (affiliate URL when present, otherwise the product URL).
 * `src` is only ever compared against a fixed whitelist, for click analytics.
 */
class MarketplaceRedirectController extends Controller
{
    public function __invoke(Request $request, ProductMarketplacePrice $marketplacePrice): RedirectResponse
    {
        $url = $marketplacePrice->displayUrl();

        abort_unless($marketplacePrice->isDisplayable() && $url !== null, 404);
        abort_unless(Product::query()->public()->whereKey($marketplacePrice->product_id)->exists(), 404);

        $source = (string) $request->query('src', '');
        $source = in_array($source, Marketplaces::clickSources(), true) ? $source : 'product_card';

        try {
            // No IP address, user agent or user id is stored — see the marketplace_clicks migration.
            MarketplaceClick::create([
                'product_id' => $marketplacePrice->product_id,
                'product_marketplace_price_id' => $marketplacePrice->id,
                'marketplace' => $marketplacePrice->marketplace,
                'source' => $source,
            ]);
        } catch (Throwable $e) {
            // Analytics must never stand between a customer and the link.
            Log::warning('marketplace.click_log_failed', ['price_id' => $marketplacePrice->id, 'error' => $e->getMessage()]);
        }

        return redirect()->away($url)->withHeaders([
            'X-Robots-Tag' => 'noindex, nofollow',
            'Cache-Control' => 'no-store, private',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveMarketplacePriceRequest;
use App\Models\Product;
use App\Models\ProductMarketplacePrice;
use App\Support\Marketplaces;
use Illuminate\Http\RedirectResponse;

/**
 * The "Marketplace Pricing" card on Admin → Catalog → Products → Edit. Each action is its own small request, so the
 * card never has to live inside (or interfere with) the product form. Prices are typed in by an admin — nothing is
 * scraped or fetched from the marketplaces.
 */
class ProductMarketplacePriceController extends Controller
{
    public function store(SaveMarketplacePriceRequest $request, Product $product): RedirectResponse
    {
        $data = $this->payload($request);
        $data['last_checked_at'] ??= now();

        $price = $product->marketplacePrices()->create($data);

        return $this->done($product, $price, Marketplaces::label($price->marketplace).' listing added.');
    }

    public function update(SaveMarketplacePriceRequest $request, Product $product, ProductMarketplacePrice $marketplacePrice): RedirectResponse
    {
        $this->ensureBelongsTo($product, $marketplacePrice);

        $data = $this->payload($request);

        // Leaving the date blank must not wipe the existing one.
        if (! $request->filled('last_checked_at')) {
            unset($data['last_checked_at']);
        }

        $marketplacePrice->fill($data);

        // Saving new numbers means an admin has just looked the price up: stamp it, unless they set a date themselves.
        if ($marketplacePrice->isDirty(['mrp', 'selling_price']) && ! $request->filled('last_checked_at')) {
            $marketplacePrice->last_checked_at = now();
        }

        $marketplacePrice->save();

        return $this->done($product, $marketplacePrice, Marketplaces::label($marketplacePrice->marketplace).' listing updated.');
    }

    public function toggle(Product $product, ProductMarketplacePrice $marketplacePrice): RedirectResponse
    {
        $this->ensureBelongsTo($product, $marketplacePrice);

        // Enabling a listing that has no selling price would publish nothing useful; ask for the price first.
        if (! $marketplacePrice->is_active && ($marketplacePrice->selling_price === null || (float) $marketplacePrice->selling_price <= 0)) {
            return $this->back($product)->with('error', 'Add a selling price before enabling this listing.');
        }

        $marketplacePrice->update(['is_active' => ! $marketplacePrice->is_active]);

        return $this->back($product)->with('success', Marketplaces::label($marketplacePrice->marketplace).($marketplacePrice->is_active ? ' listing enabled.' : ' listing disabled.'));
    }

    public function destroy(Product $product, ProductMarketplacePrice $marketplacePrice): RedirectResponse
    {
        $this->ensureBelongsTo($product, $marketplacePrice);

        $label = Marketplaces::label($marketplacePrice->marketplace);
        $marketplacePrice->delete();

        return $this->back($product)->with('success', "$label listing deleted.");
    }

    /** @return array<string, mixed> */
    private function payload(SaveMarketplacePriceRequest $request): array
    {
        $data = $request->validated();

        foreach (['marketplace_product_name', 'product_url', 'affiliate_url', 'mrp', 'selling_price', 'last_checked_at'] as $field) {
            $data[$field] = filled($data[$field] ?? null) ? trim((string) $data[$field]) : null;
        }

        $variantId = $data['product_variant_id'] ?? null;
        $data['product_variant_id'] = filled($variantId) ? (int) $variantId : null;
        // Named display_order in the form so it can never collide with the product form's own sort_order field.
        $data['sort_order'] = (int) ($data['display_order'] ?? 0);
        unset($data['display_order']);
        $data['is_active'] = (bool) $data['is_active'];

        return $data;
    }

    private function ensureBelongsTo(Product $product, ProductMarketplacePrice $price): void
    {
        abort_unless($price->product_id === $product->id, 404);
    }

    private function done(Product $product, ProductMarketplacePrice $price, string $message): RedirectResponse
    {
        $response = $this->back($product)->with('success', $message);

        if ($price->sellingPriceExceedsMrp()) {
            $response->with('warning', 'The selling price is higher than the MRP, so no discount is shown for this listing. Please double-check both values.');
        }

        return $response;
    }

    private function back(Product $product): RedirectResponse
    {
        return redirect()->to(route('admin.catalog.products.edit', $product).'#marketplace-pricing');
    }
}

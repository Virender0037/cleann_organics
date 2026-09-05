<?php

namespace App\Services\Storefront;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Centralizes the Shop and Category listing query — public-visibility rule,
 * eager loading, search/filter/sort, and pagination — so both pages share
 * one implementation instead of duplicating it.
 */
class ProductCatalogService
{
    private const PER_PAGE = 12;

    /**
     * A per-variant "starting price" used only for filtering/sorting by
     * price, not for anything shown to a shopper as a specific price. It is
     * deliberately simple: for a non-tiered variant it's single_price; for
     * a tiered variant it's the lowest of whichever tier prices are set.
     *
     * Written as a CASE waterfall rather than MySQL's LEAST()/SQLite's
     * multi-arg MIN() (the two aren't interchangeable — MySQL's MIN() is
     * aggregate-only, SQLite has no LEAST()) so the exact same SQL runs
     * against both the app's MySQL database and the in-memory SQLite used
     * by the test suite. MIN() across a product's variants, plus NULLS
     * handled explicitly, keep a product with no valid variant price out
     * of price filters/sorts rather than crashing them.
     */
    private const EFFECTIVE_PRICE_SUBQUERY = <<<'SQL'
        (
            SELECT MIN(
                CASE
                    WHEN pv.enable_tiered_pricing = 0 THEN pv.single_price
                    ELSE CASE
                        WHEN pv.single_price IS NOT NULL
                            AND (pv.standard_price IS NULL OR pv.single_price <= pv.standard_price)
                            AND (pv.discount_price IS NULL OR pv.single_price <= pv.discount_price)
                            THEN pv.single_price
                        WHEN pv.standard_price IS NOT NULL
                            AND (pv.discount_price IS NULL OR pv.standard_price <= pv.discount_price)
                            THEN pv.standard_price
                        ELSE pv.discount_price
                    END
                END
            )
            FROM product_variants pv
            WHERE pv.product_id = products.id
                AND pv.status = 'active'
                AND pv.deleted_at IS NULL
        )
        SQL;

    public function paginate(Request $request, ?Category $category = null): LengthAwarePaginator
    {
        $query = $this->baseQuery();

        if ($category) {
            $query->where('category_id', $category->id);
        } elseif ($request->filled('category')) {
            $query->whereHas('category', fn (Builder $q) => $q->active()->where('slug', $request->string('category')));
        }

        if ($request->filled('tag')) {
            $query->whereHas('tags', fn (Builder $q) => $q->where('slug', $request->string('tag')));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->string('search'));

            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('variants', fn (Builder $vq) => $vq->where('sku', 'like', "%{$search}%"));
            });
        }

        $minPrice = $request->filled('min_price') ? (float) $request->input('min_price') : null;
        $maxPrice = $request->filled('max_price') ? (float) $request->input('max_price') : null;
        $hasHaving = $request->filled('rating') || $minPrice !== null || $maxPrice !== null;

        if ($hasHaving) {
            // SQLite (used by the test suite) rejects a HAVING clause with
            // no GROUP BY when it references anything other than an
            // aggregate function — unlike MySQL, which tolerates this for
            // a plain SELECT-list alias. Grouping by the primary key is a
            // no-op for the actual result set (one row per product either
            // way) but satisfies both engines' grammar.
            $query->groupBy('products.id');
        }

        if ($request->filled('rating')) {
            $query->havingRaw('"approved_average_rating" >= CAST(? AS DECIMAL(10,2))', [$request->integer('rating')]);
        }

        // havingRaw() with the bound value wrapped in CAST(? AS DECIMAL(10,2)),
        // rather than having()'s plain "column >= ?": PDO's SQLite driver
        // binds a PHP float in a way that does not get proper numeric
        // affinity applied when compared directly against this
        // correlated-subquery alias under GROUP BY — confirmed by testing
        // (float bindings silently produced zero matching rows; the same
        // value cast in the SQL text works correctly on the exact same
        // query). DECIMAL(10,2) (matching the variant price columns'
        // precision) rather than REAL specifically because MySQL has no
        // CAST(... AS REAL) — DECIMAL is the one numeric cast type both
        // engines support, and specifying (10,2) keeps MySQL from
        // truncating to a whole number (its no-precision default).
        if ($minPrice !== null || $maxPrice !== null) {
            $query->havingRaw('"effective_min_price" >= CAST(? AS DECIMAL(10,2))', [$minPrice ?? 0]);

            if ($maxPrice !== null) {
                $query->havingRaw('"effective_min_price" <= CAST(? AS DECIMAL(10,2))', [$maxPrice]);
            }
        }

        $this->applySort($query, $request->string('sort')->toString());

        return $query->paginate(self::PER_PAGE)->withQueryString();
    }

    /**
     * The "Sale Products" sidebar widget: real best-sellers (falling back to
     * featured, then newest) instead of the old hardcoded fake products.
     * Kept separate from the main listing query — it is not affected by the
     * shopper's current filters.
     */
    public function bestSellers(int $limit = 3)
    {
        return Product::query()
            ->public()
            ->with([
                'variants' => fn ($q) => $q->where('status', 'active')->orderByDesc('is_default')->orderBy('sort_order'),
                'variants.images',
            ])
            ->orderByDesc('is_best_seller')
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * The overall [min, max] "starting price" (see EFFECTIVE_PRICE_SUBQUERY)
     * across every publicly visible product, used to bound the shop
     * sidebar's price slider. Falls back to a sane default range when the
     * catalog has no priced products yet, rather than handing the slider a
     * null/null range.
     *
     * @return array{min: float, max: float}
     */
    public function priceBounds(): array
    {
        // EFFECTIVE_PRICE_SUBQUERY is itself a correlated per-product scalar
        // subquery (already parenthesized) — it must be wrapped in its OWN
        // extra parens before an outer MIN()/MAX() aggregate can take it as
        // an argument, or the database sees a bare "MIN(SELECT ...)" and
        // rejects it as a syntax error rather than an aggregate-of-subquery.
        $row = Product::query()
            ->public()
            ->selectRaw('MIN('.self::EFFECTIVE_PRICE_SUBQUERY.') as lowest')
            ->selectRaw('MAX('.self::EFFECTIVE_PRICE_SUBQUERY.') as highest')
            ->first();

        $min = $row?->lowest !== null ? (float) $row->lowest : 0.0;
        $max = $row?->highest !== null ? (float) $row->highest : 1000.0;

        return ['min' => $min, 'max' => $max > $min ? $max : $min + 1];
    }

    /**
     * Category product counts for the sidebar, scoped to publicly visible
     * products only — a single grouped query rather than one per category.
     *
     * @return array<int, int> category_id => count
     */
    public function categoryProductCounts(): array
    {
        return Product::query()
            ->public()
            ->select('category_id', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('category_id')
            ->pluck('aggregate', 'category_id')
            ->all();
    }

    private function baseQuery(): Builder
    {
        return Product::query()
            ->public()
            ->with([
                'category:id,name,slug',
                'variants' => fn ($q) => $q->where('status', 'active')->orderByDesc('is_default')->orderBy('sort_order'),
                'variants.images',
                'tags:id,name,slug',
            ])
            ->withCount(['reviews as approved_review_count' => fn (Builder $q) => $q->where('status', 'approved')])
            ->withAvg(['reviews as approved_average_rating' => fn (Builder $q) => $q->where('status', 'approved')], 'rating')
            ->addSelect(DB::raw(self::EFFECTIVE_PRICE_SUBQUERY.' as effective_min_price'));
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'price-asc' => $query->orderByRaw('effective_min_price IS NULL')->orderBy('effective_min_price'),
            'price-desc' => $query->orderByRaw('effective_min_price IS NULL')->orderByDesc('effective_min_price'),
            'name' => $query->orderBy('name'),
            default => $query->orderByDesc('created_at'),
        };
    }
}

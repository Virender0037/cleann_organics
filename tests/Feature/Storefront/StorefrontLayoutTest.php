<?php

namespace Tests\Feature\Storefront;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Guards for layout bugs that reached production: the product-card quick-view
 * (eye) control stretching into a tall green pill, and images/logos that
 * depended on stylesheet rules and blew the page out when the stylesheet was
 * stale. These assert the markup + stylesheet contracts (a browser isn't
 * available in the suite), so a regression in either is caught.
 */
class StorefrontLayoutTest extends TestCase
{
    use RefreshDatabase;

    private function stylesheet(): string
    {
        return file_get_contents(public_path('css/style.css'));
    }

    private function productWithVariant(string $name = 'Layout Cleaner'): Product
    {
        $category = Category::create(['name' => 'Cleaners', 'slug' => 'cleaners-'.uniqid(), 'status' => 'active']);
        $product = Product::create([
            'category_id' => $category->id, 'name' => $name, 'slug' => Str::slug($name).'-'.uniqid(),
            'status' => 'active', 'is_returnable' => false, 'return_days' => 7,
        ]);
        $product->variants()->create([
            'variant_name' => 'Variant', 'enable_tiered_pricing' => false, 'single_quantity' => 1, 'single_price' => 99,
            'stock_quantity' => 10, 'low_stock_quantity' => 5, 'stock_status' => 'in_stock', 'is_default' => true,
            'status' => 'active', 'sort_order' => 0,
        ]);

        return $product;
    }

    public function test_card_hover_actions_are_two_separate_controls_and_add_to_cart_is_outside_them(): void
    {
        $this->productWithVariant();

        $html = $this->get('/shop')->assertOk()->getContent();

        // Wishlist + quick-view live in the hover list; add-to-cart is its own control in the info row.
        $this->assertMatchesRegularExpression('~<div class="cards-md__favs-list">(?:(?!</div>).)*?action-btn(?:(?!</div>).)*?action-btn~s', $html);
        $this->assertSame(2, preg_match_all('~class="action-btn"~', $this->between($html, 'cards-md__favs-list', 'cards-md__info d-flex')));
        $this->assertStringContainsString('cards-md__info-right', $html);
        $this->assertMatchesRegularExpression('~cards-md__info-right">\s*<form[^>]*data-cart-form="add"[^>]*>.*?class="action-btn"~s', $html);
    }

    /**
     * The eye is an <a class="action-btn">. A descendant rule like
     * ".cards-md__img-wrapper a { width:100%; height:230px }" (meant only for
     * the product image link) also matched it and stretched it into a tall
     * pill. Those rules must target the direct-child image link only.
     */
    public function test_card_image_link_rules_do_not_leak_onto_the_action_buttons(): void
    {
        $css = $this->stylesheet();

        $this->assertDoesNotMatchRegularExpression('~\.cards-md(?:--\w+)? \.cards-md__img-wrapper a[\s,{]~', $css, 'Descendant "a" selector would restyle the quick-view button.');
        $this->assertStringContainsString('.cards-md__img-wrapper > a {', $css);
        $this->assertStringContainsString('.cards-md--four .cards-md__img-wrapper > a {', $css);
        // The circular 40px action button definition stays intact.
        $this->assertMatchesRegularExpression('~\.action-btn \{\s*width: 40px;\s*height: 40px;\s*border-radius: 100%;~', $css);
    }

    public function test_header_and_footer_logos_link_home_and_are_size_capped_inline(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('~<a href="[^"]*" class="header__logo-link"[^>]*>\s*<img class="header__logo" style="max-width:min\(260px,100%\);max-height:72px;"~', $html);
        $this->assertMatchesRegularExpression('~footer__brand-info-logo">\s*<a href="[^"]*"[^>]*>\s*<img[^>]*style="max-width:min\(240px,100%\);max-height:72px;"~', $html);
    }

    public function test_storefront_stylesheet_and_scripts_are_cache_busted(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('~css/style\.css\?v=\d+~', $html);
        $this->assertMatchesRegularExpression('~js/home1\.js\?v=\d+~', $html);
        $this->assertMatchesRegularExpression('~js/cart\.js\?v=\d+~', $html);
    }

    public function test_new_homepage_images_are_guarded_against_natural_size_overflow(): void
    {
        $this->productWithVariant('Cheap Thing');
        \App\Models\HomeBanner::create(['section' => 'hero', 'title' => 'Slide', 'image' => 'home-banners/x.jpg', 'link_type' => 'url', 'link_url' => '/shop', 'sort_order' => 0, 'status' => 'active']);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('~<img src="[^"]*" alt="" loading="lazy" style="max-width:100%;">~', $html, 'Explore Our Range image');
        $this->assertMatchesRegularExpression('~<img src="[^"]*" alt="[^"]*" style="max-width:100%;"~', $html, 'Hero image');
    }

    public function test_layout_fixes_from_the_responsive_audit_are_in_the_stylesheet(): void
    {
        $css = $this->stylesheet();

        $this->assertStringContainsString('.header__brand { min-width: 0; }', $css);
        $this->assertStringContainsString('.cards-tm__info--user-img { flex-shrink: 0; }', $css);
        $this->assertStringContainsString('.cards-blog__img-wrapper > a { display: block; width: 100%; height: 100%; }', $css);
        $this->assertStringContainsString('.header__logo { height: auto;', $css);
        // No global overflow hack hiding real layout bugs.
        $this->assertDoesNotMatchRegularExpression('~\bbody\s*\{[^}]*overflow-x:\s*hidden~', $css);
    }

    private function between(string $haystack, string $start, string $end): string
    {
        $a = strpos($haystack, $start);
        $b = strpos($haystack, $end, $a ?: 0);

        return substr($haystack, $a, ($b ?: strlen($haystack)) - $a);
    }
}

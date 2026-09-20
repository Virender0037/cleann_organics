<?php

namespace Tests\Feature\Storefront;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\HomeBanner;
use App\Models\Page;
use App\Models\TeamMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Template leftovers that shipped to production as if they were real company
 * content (stock team members, fake partner logos, a fake Instagram gallery, dead
 * "#" links) — and the guards that keep them out.
 */
class StorefrontContentTest extends TestCase
{
    use RefreshDatabase;

    private function aboutPage(): void
    {
        Page::create(['title' => 'About Us', 'slug' => 'about-us', 'content' => 'x', 'status' => 'active']);
    }

    private function blog(): Blog
    {
        $category = BlogCategory::create(['name' => 'Eco', 'slug' => 'eco-'.uniqid(), 'status' => 'active']);

        return Blog::create([
            'blog_category_id' => $category->id, 'title' => 'A Real Post', 'slug' => 'a-real-post', 'short_description' => 'Short.',
            'content' => '<p>Body</p>', 'status' => 'published', 'published_at' => now()->subDay(),
        ]);
    }

    private function member(array $overrides = []): TeamMember
    {
        return TeamMember::create(array_merge(['name' => 'Asha Verma', 'designation' => 'Founder', 'status' => 'active', 'sort_order' => 1], $overrides));
    }

    public function test_home_and_about_no_longer_show_the_template_brand_logo_strip(): void
    {
        $this->aboutPage();

        foreach (['/', '/about-us'] as $url) {
            $this->get($url)->assertOk()->assertDontSee('brand-name', false);
        }
    }

    public function test_about_shows_real_team_members_and_never_the_template_stock_team(): void
    {
        $this->aboutPage();
        $this->member(['name' => 'Asha Verma', 'facebook_url' => 'https://facebook.com/asha']);
        $this->member(['name' => 'Hidden Person', 'status' => 'inactive', 'sort_order' => 2]);

        $response = $this->get('/about-us')->assertOk();

        $response->assertSee('Meet Our Team')->assertSee('Asha Verma')->assertSee('Founder')
            ->assertSee('href="https://facebook.com/asha" target="_blank" rel="noopener noreferrer"', false)
            ->assertDontSee('Hidden Person')
            ->assertDontSee('Cody Fisher')->assertDontSee('Jane Cooper')->assertDontSee('Jenny Wilson')->assertDontSee('Robert Fox')
            ->assertDontSee('Our Awesome Team')->assertDontSee('Pellentesque');
        // A member without a social URL gets no dead icon for it.
        $this->assertSame(1, substr_count($response->getContent(), 'on Facebook'));
        $this->assertStringNotContainsString('on Instagram', $response->getContent());
    }

    public function test_about_hides_the_team_section_entirely_when_there_are_no_members(): void
    {
        $this->aboutPage();

        $this->get('/about-us')->assertOk()->assertDontSee('Meet Our Team')->assertDontSee('cards-mb', false);
    }

    public function test_about_uses_the_real_benefits_strip_instead_of_template_feature_cards(): void
    {
        $this->aboutPage();
        HomeBanner::create(['section' => 'benefit', 'title' => 'Free Shipping', 'subtitle' => 'Free Shipping on Orders Above ₹{free_shipping_threshold}', 'icon' => 'truck', 'link_type' => 'none', 'status' => 'active']);

        $this->get('/about-us')->assertOk()
            ->assertSee('home-benefits', false)->assertSee('Free Shipping on Orders Above ₹399')
            ->assertDontSee('100% Organic Food')->assertDontSee('Sucure');
    }

    public function test_about_has_no_dead_links_and_shop_now_goes_to_the_shop(): void
    {
        $this->aboutPage();
        $this->member(['facebook_url' => 'https://facebook.com/x']);

        $html = $this->get('/about-us')->assertOk()->getContent();

        $main = substr($html, strpos($html, '<main>'), strpos($html, '</main>') - strpos($html, '<main>'));
        $this->assertStringNotContainsString('href="#"', $main, 'No dead # links in the About page content.');
        $this->assertMatchesRegularExpression('~<a href="[^"]*/shop" class="button button--md">\s*Shop Now~i', $html);
    }

    public function test_blog_pages_no_longer_show_the_fake_instagram_gallery(): void
    {
        $blog = $this->blog();

        $this->get('/bloglist')->assertOk()->assertDontSee('Our Gallery')->assertDontSee('cards-ig', false);
        $this->get('/singleblog/'.$blog->slug)->assertOk()->assertDontSee('Our Gallery')->assertDontSee('cards-ig', false);
    }

    public function test_blog_share_icons_are_real_share_links_for_this_post(): void
    {
        $blog = $this->blog();

        $html = $this->get('/singleblog/'.$blog->slug)->assertOk()->getContent();
        $encoded = urlencode(url('/singleblog/'.$blog->slug));

        $this->assertStringContainsString('https://www.facebook.com/sharer/sharer.php?u='.$encoded, $html);
        $this->assertStringContainsString('https://twitter.com/intent/tweet?url='.$encoded, $html);
        $this->assertStringContainsString('https://pinterest.com/pin/create/button/?url='.$encoded, $html);
        $this->assertSame(3, substr_count($html, 'rel="noopener noreferrer" aria-label="Share on'));
        // The old dead icons (including Instagram, which has no share endpoint) are gone.
        $this->assertDoesNotMatchRegularExpression('~<li class="social-icon-link">\s*<a href="#">~', $html);
    }

    public function test_home_latest_news_falls_back_to_real_blog_posts_never_the_template_cards(): void
    {
        $this->blog();

        $response = $this->get('/')->assertOk();

        $response->assertSee('Latest News')->assertSee('A Real Post')
            ->assertDontSee('Curabitur')->assertDontSee('65 Comments')
            ->assertDontSee('single-blog.html', false);
    }

    public function test_home_hides_latest_news_when_there_is_no_feed_and_no_blog_post(): void
    {
        $this->get('/')->assertOk()->assertDontSee('Latest News')->assertDontSee('Curabitur')->assertDontSee('news-slider', false);
    }

    public function test_home_news_swiper_markup_is_balanced(): void
    {
        $this->blog();

        $html = $this->get('/')->assertOk()->getContent();
        $news = substr($html, strpos($html, 'class="news section'), strpos($html, 'class="testimonial') - strpos($html, 'class="news section'));

        $this->assertSame(substr_count($news, '<div'), substr_count($news, '</div>'), 'Latest News section must have balanced <div> tags (swiper-wrapper used to stay open).');
    }

    public function test_storefront_pages_link_only_to_real_routes_not_template_html_files(): void
    {
        $this->aboutPage();
        Page::create(['title' => 'Contact Us', 'slug' => 'contact-us', 'content' => 'x', 'status' => 'active']);

        foreach (['/', '/about-us', '/contact-us', '/faq', '/sign-in', '/create-account', '/bloglist', '/shop', '/definitely-missing'] as $url) {
            $html = $this->get($url)->getContent();

            $this->assertDoesNotMatchRegularExpression('~href="[^"]*\.html"~', $html, "Template .html link on {$url}");
        }
    }

    public function test_blog_list_has_no_leftover_static_mini_cart(): void
    {
        $this->blog();

        $html = $this->get('/bloglist')->assertOk()->getContent();

        $this->assertStringNotContainsString('$26.00', $html);
        $this->assertSame(1, substr_count($html, 'class="shopping-cart"'), 'Only the real mini-cart drawer may exist.');
    }

    public function test_product_card_stylesheet_contracts_for_touch_and_consistent_ratio(): void
    {
        $css = file_get_contents(public_path('css/style.css'));

        $this->assertStringContainsString('@media (hover: none), (max-width: 991px) {', $css);
        $this->assertMatchesRegularExpression('~\.cards-md \.cards-md__favs-list \{ opacity: 1; visibility: visible; pointer-events: all; \}~', $css);
        $this->assertStringContainsString('.cards-md--four .cards-md__img-wrapper > a { height: auto; aspect-ratio: 1 / 1; }', $css);
        $this->assertStringContainsString('min-height: 2lh;', $css, 'Card names reserve two lines so card heights stay equal.');
    }
}

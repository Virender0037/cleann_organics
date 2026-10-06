<?php

namespace Tests\Feature\Storefront;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductMarketplacePrice;
use App\Models\Setting;
use App\Models\User;
use App\Support\LogoTrimmer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Regressions for the "tiny logo" (padded logo canvas + dead mobile CSS rule) and the fake Shop "79% Discount"
 * template banner.
 */
class LogoAndShopBannerTest extends TestCase
{
    use RefreshDatabase;

    /** A transparent canvas with an opaque artwork band — like the live logo (artwork ≈ 25% of the height). */
    private function paddedPng(string $path, int $width = 800, int $height = 534, array $art = [30, 192, 770, 330]): void
    {
        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
        imagefilledrectangle($image, $art[0], $art[1], $art[2], $art[3], imagecolorallocatealpha($image, 30, 120, 40, 0));
        imagepng($image, $path);
        imagedestroy($image);
    }

    // ------------------------------------------------------------------ logo trimming

    public function test_a_padded_transparent_logo_is_trimmed_to_its_artwork_without_distortion(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'logo').'.png';
        $this->paddedPng($path);

        $this->assertTrue(LogoTrimmer::trim($path));

        [$width, $height] = getimagesize($path);
        // Artwork 741×139 plus a 3% margin each side; the canvas was 800×534.
        $this->assertLessThan(534 * 0.4, $height);
        $this->assertGreaterThanOrEqual(139, $height);
        $this->assertGreaterThanOrEqual(741, $width);
        $this->assertEqualsWithDelta(741 / 139, ($width - 2 * 23) / ($height - 2 * 23), 0.2); // aspect preserved

        // Transparency is preserved in the margin.
        $image = imagecreatefrompng($path);
        $this->assertSame(127, (imagecolorat($image, 0, 0) >> 24) & 0x7F);
        imagedestroy($image);

        // Idempotent: a second pass finds nothing worth trimming.
        $this->assertFalse(LogoTrimmer::trim($path));
    }

    public function test_an_already_tight_blank_or_unsupported_image_is_left_untouched(): void
    {
        $tight = tempnam(sys_get_temp_dir(), 'tight').'.png';
        $this->paddedPng($tight, 400, 100, [2, 2, 397, 97]);
        $before = md5_file($tight);
        $this->assertFalse(LogoTrimmer::trim($tight));
        $this->assertSame($before, md5_file($tight));

        $blank = tempnam(sys_get_temp_dir(), 'blank').'.png';
        $this->paddedPng($blank, 200, 200, [-1, -1, -1, -1]);
        $this->assertFalse(LogoTrimmer::trim($blank));

        $jpeg = tempnam(sys_get_temp_dir(), 'jpg').'.jpg';
        $image = imagecreatetruecolor(300, 300);
        imagejpeg($image, $jpeg);
        $this->assertFalse(LogoTrimmer::trim($jpeg));

        $this->assertFalse(LogoTrimmer::trim(sys_get_temp_dir().'/does-not-exist.png'));
    }

    public function test_uploading_a_padded_logo_in_admin_stores_the_trimmed_version_but_not_the_favicon(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'superadmin']);

        $logoPath = tempnam(sys_get_temp_dir(), 'up').'.png';
        $this->paddedPng($logoPath);
        $faviconPath = tempnam(sys_get_temp_dir(), 'fav').'.png';
        $this->paddedPng($faviconPath, 128, 128, [40, 40, 87, 87]);

        $this->actingAs($admin)->put(route('admin.settings.general.update'), [
            'site_name' => 'CleannOrganics', 'timezone' => 'Asia/Kolkata', 'currency' => 'INR', 'language' => 'en',
            'logo' => new UploadedFile($logoPath, 'logo.png', 'image/png', null, true),
            'favicon' => new UploadedFile($faviconPath, 'favicon.png', 'image/png', null, true),
        ])->assertSessionHasNoErrors();

        $settings = Setting::group('general');
        [$logoW, $logoH] = getimagesize(Storage::disk('public')->path($settings['logo']));
        [$favW, $favH] = getimagesize(Storage::disk('public')->path($settings['favicon']));

        $this->assertLessThan(534 * 0.4, $logoH);   // trimmed
        $this->assertSame([128, 128], [$favW, $favH]); // favicon keeps its square canvas
    }

    // ------------------------------------------------------------------ logo markup / CSS

    public function test_header_and_footer_logos_keep_their_overflow_guards_and_link_home(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('~<a href="'.preg_quote(route('home'), '~').'" class="header__logo-link"[^>]*>\s*<img class="header__logo" style="max-width:min\(260px,100%\);max-height:72px;"~', $html);
        $this->assertMatchesRegularExpression('~footer__brand-info-logo">\s*<a href="'.preg_quote(route('home'), '~').'"[^>]*>\s*<img [^>]*style="max-width:min\(240px,100%\);max-height:72px;"~', $html);
    }

    public function test_mobile_logo_sizes_override_the_inline_guard(): void
    {
        foreach (['public/css/style.css', 'public/scss/components/_storefront-offers.scss'] as $file) {
            $css = file_get_contents(base_path($file));

            $this->assertStringContainsString('@media (max-width: 991px) { .header__logo { max-height: 44px !important; max-width: min(190px, 100%) !important; } }', $css, $file);
            $this->assertStringContainsString('@media (max-width: 380px) { .header__logo { max-height: 38px !important; max-width: min(165px, 100%) !important; } }', $css, $file);
            $this->assertStringNotContainsString('max-height: 52px; max-width: min(190px, 100%); } }', $css, $file); // the old, dead rule
            // Dark footer + dark wordmark: the footer logo carries its own light backing, bounded by the inline guard.
            $this->assertStringContainsString('object-position: left center; box-sizing: border-box; background: #fff; padding: 8px 12px; border-radius: 8px; }', $css, $file);
        }
    }

    // ------------------------------------------------------------------ shop banner / discounts

    public function test_shop_has_no_fake_79_percent_template_banner(): void
    {
        $html = $this->get(route('shop'))->assertOk()->getContent();

        $this->assertStringNotContainsString('banner-sm-19.jpg', $html);
        $this->assertStringNotContainsString('<span>79%</span> Discount', $html);
        $this->assertStringNotContainsString('on Your Fast Order', $html);
    }

    public function test_marketplace_discount_badge_math_for_large_and_rounding_cases(): void
    {
        $category = Category::create(['name' => 'C', 'slug' => 'c', 'status' => 'active']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'P', 'slug' => 'p', 'status' => 'active', 'is_returnable' => false, 'return_days' => 7]);
        $offer = fn (array $values) => new ProductMarketplacePrice($values + ['product_id' => $product->id, 'marketplace' => 'amazon']);

        $this->assertSame(82, $offer(['mrp' => 599, 'selling_price' => 105])->discountPercent());  // (599-105)/599 = 82.47%
        $this->assertSame(79, $offer(['mrp' => 100, 'selling_price' => 21])->discountPercent());   // a genuine 79%
        $this->assertSame(1, $offer(['mrp' => 100, 'selling_price' => 99])->discountPercent());
        $this->assertNull($offer(['mrp' => 100, 'selling_price' => 99.6])->discountPercent());    // rounds to 0 → no badge
        $this->assertNull($offer(['mrp' => -10, 'selling_price' => 5])->discountPercent());       // invalid MRP
        $this->assertNull($offer(['mrp' => 100, 'selling_price' => null])->discountPercent());
    }
}

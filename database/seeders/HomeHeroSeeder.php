<?php

namespace Database\Seeders;

use App\Models\HomeBanner;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Three starter hero slides built from the Clean Organics lifestyle
 * photography (web-optimised WebP copies live in database/seeders/assets/
 * home-hero/; the originals are not committed).
 *
 * Idempotent and safe on production: seeds ONCE (flag recorded) and only
 * into an empty hero section, copying each image onto the `public` disk
 * under home-banners/. After that the slides are ordinary admin-managed rows
 * (Admin → CMS → Hero Slides) — never overwritten or recreated by re-runs.
 */
class HomeHeroSeeder extends Seeder
{
    private const FLAG = 'home_hero_seeded';

    public function run(): void
    {
        if (Setting::where('key', self::FLAG)->exists()) {
            return;
        }

        if (! HomeBanner::query()->section(HomeBanner::SECTION_HERO)->exists()) {
            $slides = [
                [
                    'title' => 'Bio-enzyme cleaners & everyday eco essentials',
                    'subtitle' => 'Plant-based care for a cleaner home',
                    'button_text' => 'Shop Now',
                    'alt_text' => 'Cleann Organics lemon floor cleaner in a basket with a copper bottle and bamboo brushes',
                ],
                [
                    'title' => 'Rose, Lemon & Lavender Floor Cleaners',
                    'subtitle' => 'Bio-enzyme formulas in three fragrances',
                    'button_text' => 'Shop Now',
                    'alt_text' => 'A hand holding Cleann Organics rose, lemon and lavender floor cleaners with natural cleaning tools',
                ],
                [
                    'title' => 'Cleaner floors, happier homes',
                    'subtitle' => 'Explore the Cleann Organics range',
                    'button_text' => 'Explore the range',
                    'alt_text' => 'A delighted woman lifting a bag as Cleann Organics floor cleaner bottles fall out',
                ],
            ];

            foreach ($slides as $i => $slide) {
                $n = $i + 1;

                HomeBanner::create($slide + [
                    'section' => HomeBanner::SECTION_HERO,
                    'image' => $this->copyAsset("hero-$n-desktop.webp"),
                    'mobile_image' => $this->copyAsset("hero-$n-mobile.webp"),
                    'link_type' => 'url',
                    'link_url' => '/shop',
                    'opens_new_tab' => false,
                    'sort_order' => $n * 10,
                    'status' => 'active',
                ]);
            }
        }

        Setting::create(['key' => self::FLAG, 'value' => '1', 'group' => 'system']);
    }

    /** Copies a bundled seed image onto the public disk and returns its disk path. */
    private function copyAsset(string $file): ?string
    {
        $source = database_path('seeders/assets/home-hero/'.$file);

        if (! is_file($source)) {
            return null;
        }

        $path = 'home-banners/seed-'.$file;
        Storage::disk('public')->put($path, file_get_contents($source));

        return $path;
    }
}

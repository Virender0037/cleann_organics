<?php

namespace Database\Seeders;

use App\Models\HomeBanner;
use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Starter cards for the homepage Benefits Strip (Admin → CMS → Benefits Strip).
 *
 * Idempotent and safe to run on production repeatedly: it seeds ONCE (a
 * `home_benefits_seeded` flag is recorded) and only into an empty section, so
 * it never duplicates cards and never resurrects or overwrites anything an
 * admin later edits or deletes.
 */
class HomeBenefitSeeder extends Seeder
{
    private const FLAG = 'home_benefits_seeded';

    public function run(): void
    {
        if (Setting::where('key', self::FLAG)->exists()) {
            return;
        }

        if (! HomeBanner::query()->section(HomeBanner::SECTION_BENEFIT)->exists()) {
            $cards = [
                ['icon' => 'truck', 'title' => 'Free Shipping', 'subtitle' => 'Free Shipping on Orders Above ₹'.HomeBanner::THRESHOLD_TOKEN, 'link_type' => 'none', 'link_url' => null],
                ['icon' => 'headset', 'title' => 'Customer Support 24/7', 'subtitle' => 'Instant access to support', 'link_type' => 'url', 'link_url' => '/contact-us'],
                ['icon' => 'shield', 'title' => '100% Secure Payment', 'subtitle' => 'We ensure your money is safe', 'link_type' => 'none', 'link_url' => null],
                ['icon' => 'money-back', 'title' => 'Money Back Guarantee', 'subtitle' => '30 days money-back guarantee', 'link_type' => 'none', 'link_url' => null],
            ];

            foreach ($cards as $i => $card) {
                HomeBanner::create($card + [
                    'section' => HomeBanner::SECTION_BENEFIT,
                    'sort_order' => ($i + 1) * 10,
                    'status' => 'active',
                    'opens_new_tab' => false,
                ]);
            }
        }

        Setting::create(['key' => self::FLAG, 'value' => '1', 'group' => 'system']);
    }
}

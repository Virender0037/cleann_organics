<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    /**
     * Seed the 16 CleannOrganics customer testimonials.
     *
     * Matches existing rows by exact name + message and updates them in place
     * (preserving their id); inserts any that do not already exist. Safe to
     * re-run — never creates duplicates, never truncates, and leaves every
     * other testimonial row (including the soft-deleted id=1) untouched.
     * company is stored as NULL for all of these records.
     */
    public function run(): void
    {
        foreach ($this->testimonials() as $testimonial) {
            Testimonial::updateOrCreate(
                [
                    'name' => $testimonial['name'],
                    'message' => $testimonial['message'],
                ],
                [
                    'designation' => $testimonial['designation'],
                    'city' => $testimonial['city'],
                    'company' => null,
                    'image' => $testimonial['image'],
                    'rating' => $testimonial['rating'],
                    'sort_order' => $testimonial['sort_order'],
                    'is_featured' => $testimonial['is_featured'],
                    'status' => $testimonial['status'],
                ]
            );
        }
    }

    /**
     * @return array<int, array{name: string, designation: string, city: string, image: string, rating: int, message: string, sort_order: int, is_featured: bool, status: string}>
     */
    private function testimonials(): array
    {
        return [
            [
                'name' => 'Priya Sharma',
                'designation' => 'Homemaker',
                'city' => 'Delhi',
                'image' => 'testimonials/priya-sharma.jpg',
                'rating' => 5,
                'message' => 'I have been using the Lavender Bio-enzyme Floor Cleaner for three months now and I am absolutely in love! My floors feel genuinely clean without that harsh chemical smell, and the lavender fragrance is so calming. I have a toddler at home who crawls everywhere, so knowing the floor is completely chemical-free gives me such peace of mind. CleannOrganics has truly changed the way I clean my home!',
                'sort_order' => 1,
                'is_featured' => true,
                'status' => 'active',
            ],
            [
                'name' => 'Ankit Mehta',
                'designation' => 'Wellness Enthusiast',
                'city' => 'Mumbai',
                'image' => 'testimonials/ankit-mehta.jpg',
                'rating' => 5,
                'message' => 'The Copper Travel Bottle from CleannOrganics is absolutely beautiful — and it truly works! I fill it overnight and drink the copper-enriched water first thing in the morning as part of my Ayurvedic wellness routine. I have noticed significantly better digestion and feel much more energetic throughout the day. The quality is exceptional and it has not leaked even once. Highly recommend to anyone serious about natural wellness!',
                'sort_order' => 2,
                'is_featured' => true,
                'status' => 'active',
            ],
            [
                'name' => 'Radha Krishnan',
                'designation' => 'Retired Teacher',
                'city' => 'Bangalore',
                'image' => 'testimonials/radha-krishnan.jpg',
                'rating' => 5,
                'message' => 'I am 68 years old and have used chemical toothpastes all my life. My dentist suggested switching to something more natural and I discovered CleannOrganics Ayurvedic Manjan. The change has been remarkable — my gums feel healthier, my breath is consistently fresher, and I love the natural mint and clove taste. This takes me right back to the traditional oral care our elders swore by. A truly wonderful product!',
                'sort_order' => 3,
                'is_featured' => true,
                'status' => 'active',
            ],
            [
                'name' => 'Kavya Nair',
                'designation' => 'Working Mother',
                'city' => 'Pune',
                'image' => 'testimonials/kavya-nair.jpg',
                'rating' => 4,
                'message' => 'I bought the Bamboo Baby Toothbrush for my 2-year-old and I am really impressed. The bristles are incredibly soft — my daughter does not fuss at all during brushing anymore! It is plastic-free and completely safe, which gives me immense peace of mind as a mother. My only suggestion would be to offer it in fun colours for children. Otherwise a truly perfect product that every parent should try!',
                'sort_order' => 4,
                'is_featured' => false,
                'status' => 'active',
            ],
            [
                'name' => 'Rajesh Kumar',
                'designation' => 'Software Engineer',
                'city' => 'Hyderabad',
                'image' => 'testimonials/rajesh-kumar.jpg',
                'rating' => 5,
                'message' => 'As someone who works long hours and does not have time for complicated cleaning routines, the Lemon Bio-enzyme Floor Cleaner has been a total lifesaver. Just dilute and mop — it is that simple! My floors are spotlessly clean, the citrus lemon scent is so refreshing, and knowing there are zero toxic chemicals involved is a huge plus. Delivery was fast and packaging was eco-friendly. Great product from a great brand!',
                'sort_order' => 5,
                'is_featured' => false,
                'status' => 'active',
            ],
            [
                'name' => 'Meera Sundaram',
                'designation' => 'Environmental Consultant',
                'city' => 'Chennai',
                'image' => 'testimonials/meera-sundaram.jpg',
                'rating' => 4,
                'message' => 'I have been on a plastic-free journey for the past year and the Coconut Coir Scrubber is one of the best switches I have made. It scrubs amazingly well — even better than my old synthetic sponge — and knowing it is fully biodegradable makes me feel wonderful. The Pack of 10 is outstanding value. My only suggestion is to add a small hanging loop for easier drying. Otherwise, absolutely brilliant!',
                'sort_order' => 6,
                'is_featured' => false,
                'status' => 'active',
            ],
            [
                'name' => 'Sunita Ghosh',
                'designation' => 'Homemaker',
                'city' => 'Kolkata',
                'image' => 'testimonials/sunita-ghosh.jpg',
                'rating' => 5,
                'message' => 'The Rose Bio-enzyme Floor Cleaner is truly magical! My home smells like a beautiful garden after every mopping session. My guests always ask what perfume I use — they simply cannot believe it is just the floor cleaner! It is made with real rose fragrance and natural ingredients — no burning eyes, no chemical smell, and no worries about my grandchildren crawling on the floor. Simply love everything about it!',
                'sort_order' => 7,
                'is_featured' => false,
                'status' => 'active',
            ],
            [
                'name' => 'Aryan Gupta',
                'designation' => 'College Student',
                'city' => 'Delhi',
                'image' => 'testimonials/aryan-gupta.jpg',
                'rating' => 3,
                'message' => 'The Organic Earbuds are a solid eco-friendly product and I am happy to ditch plastic cotton buds for good. The bamboo stick feels sturdy and the cotton is soft. My only feedback is that I wish there were more earbuds per box for the price point — felt a little less for a student\'s budget. That said, I will keep buying because I genuinely care about reducing plastic waste. Keep improving and this will easily be a 5-star product!',
                'sort_order' => 8,
                'is_featured' => false,
                'status' => 'active',
            ],
            [
                'name' => 'Sunita Patel',
                'designation' => 'Nutritionist',
                'city' => 'Mumbai',
                'image' => 'testimonials/sunita-patel.jpg',
                'rating' => 5,
                'message' => 'As a nutritionist, I recommend copper water to many of my clients, and the CleannOrganics Copper Travel Bottle is the one I personally use and always suggest. The quality of the copper is outstanding — no smell, no leakage, keeps water cool beautifully. I have already ordered three more as gifts for family members. If you are serious about Ayurvedic wellness and natural health, this is an absolute must-have in your daily routine!',
                'sort_order' => 9,
                'is_featured' => false,
                'status' => 'active',
            ],
            [
                'name' => 'Vikram Rao',
                'designation' => 'Sustainability Blogger',
                'city' => 'Bangalore',
                'image' => 'testimonials/vikram-rao.jpg',
                'rating' => 4,
                'message' => 'Made the switch from plastic toothbrushes to the CleannOrganics Bamboo Toothbrush and I am genuinely impressed. The bristles are soft yet effective, and the bamboo handle has a premium feel. Knowing that when this brush reaches end of life it goes back to the earth is deeply satisfying. Would love to see a slightly firmer bristle option in future. Overall an excellent product that I will absolutely keep coming back to!',
                'sort_order' => 10,
                'is_featured' => false,
                'status' => 'active',
            ],
            [
                'name' => 'Deepika Verma',
                'designation' => 'Mother of Three',
                'city' => 'Noida',
                'image' => 'testimonials/deepika-verma.jpg',
                'rating' => 5,
                'message' => 'With three kids at home — aged 2, 5, and 8 — I was always deeply worried about the chemical cleaners on our floors. My youngest crawls everywhere! The CleannOrganics Lavender Bio-enzyme Floor Cleaner has been an absolute blessing for our family. It cleans beautifully, smells divine, and I finally have complete peace of mind that my babies are safe. Thank you, CleannOrganics — you are doing something truly important for Indian families!',
                'sort_order' => 11,
                'is_featured' => false,
                'status' => 'active',
            ],
            [
                'name' => 'Rahul Sharma',
                'designation' => 'IT Professional (gifted to mother)',
                'city' => 'Jaipur',
                'image' => 'testimonials/rahul-sharma.jpg',
                'rating' => 5,
                'message' => 'I gifted the CleannOrganics Ayurvedic Manjan and Bamboo Toothbrush combo to my mother on her birthday and she absolutely loved it! She was instantly reminded of the traditional dant manjan her own mother used to make at home. She has been using it every day since and her dentist was genuinely surprised at the improvement in her gum health at her last checkup. The best gifting idea for mothers who love everything natural and wholesome!',
                'sort_order' => 12,
                'is_featured' => false,
                'status' => 'active',
            ],
            [
                'name' => 'Kamala Devi',
                'designation' => 'Retired Homemaker',
                'city' => 'Pune',
                'image' => 'testimonials/kamala-devi.jpg',
                'rating' => 4,
                'message' => 'My granddaughter introduced me to CleannOrganics and I am so grateful she did! I tried the Coconut Coir Scrubber expecting it to be too rough for my delicate vessels, but it is actually perfect — tough on grease yet gentle on the surface. I dry it in the sun as suggested and it lasts very well. At my age, knowing I am using something completely natural and safe in my kitchen makes all the difference in the world!',
                'sort_order' => 13,
                'is_featured' => false,
                'status' => 'active',
            ],
            [
                'name' => 'Nisha Patel',
                'designation' => 'Fashion Designer',
                'city' => 'Ahmedabad',
                'image' => 'testimonials/nisha-patel.jpg',
                'rating' => 5,
                'message' => 'I am very particular about everything in my home and the CleannOrganics Rose Bio-enzyme Floor Cleaner is hands down the most luxurious yet responsible cleaning product I have ever used. The white rose fragrance is simply elegant — my home always smells beautiful after mopping. And knowing it is completely chemical-free and eco-friendly aligns perfectly with my values as a conscious consumer. Absolutely in love with this brand!',
                'sort_order' => 14,
                'is_featured' => false,
                'status' => 'active',
            ],
            [
                'name' => 'Preethi Rajan',
                'designation' => 'Design Student',
                'city' => 'Chennai',
                'image' => 'testimonials/preethi-rajan.jpg',
                'rating' => 3,
                'message' => 'I appreciate the eco-friendly concept deeply and I am glad I made the switch from plastic. The brush works well and the bamboo handle is comfortable to hold. My honest feedback is that the bristles could be a little firmer — I personally prefer a stronger cleaning feel. Shipping also took slightly longer than expected to reach Chennai. Overall a good product with a genuinely great purpose and I will continue buying to support plastic-free brands!',
                'sort_order' => 15,
                'is_featured' => false,
                'status' => 'active',
            ],
            [
                'name' => 'Geeta Malhotra',
                'designation' => 'Homemaker',
                'city' => 'Delhi',
                'image' => 'testimonials/geeta-malhotra.jpg',
                'rating' => 5,
                'message' => 'I discovered CleannOrganics through a friend and have not looked back since! I have now tried the Copper Travel Bottle, Lavender Floor Cleaner, and Ayurvedic Manjan — and all three are truly outstanding. Quality is exceptional, delivery was prompt, and the packaging is thoughtfully eco-friendly. What truly sets this brand apart is their genuine commitment to natural, chemical-free living. CleannOrganics has earned a completely loyal customer in me for life!',
                'sort_order' => 16,
                'is_featured' => false,
                'status' => 'active',
            ],
        ];
    }
}

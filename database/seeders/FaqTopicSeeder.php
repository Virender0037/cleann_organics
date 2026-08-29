<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqTopicSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Matches existing FAQs by exact question text and updates them in place
     * (preserving their id); inserts any question that does not already exist.
     * Safe to re-run — never creates duplicates.
     */
    public function run(): void
    {
        foreach ($this->faqs() as $faq) {
            Faq::updateOrCreate(
                ['question' => $faq['question']],
                [
                    'answer' => $faq['answer'],
                    'topic' => $faq['topic'],
                    'sort_order' => $faq['sort_order'],
                    'status' => $faq['status'],
                ]
            );
        }
    }

    /**
     * @return array<int, array{question: string, answer: string, topic: string, sort_order: int, status: string}>
     */
    private function faqs(): array
    {
        return [
            // Products & Ingredients
            [
                'question' => 'Are CleannOrganics products 100% natural and chemical-free?',
                'answer' => 'Yes, absolutely! Every product at CleannOrganics is carefully crafted using 100% natural, plant-based ingredients. We never use harsh synthetic chemicals, artificial preservatives, parabens, sulfates, or toxic additives. Our formulations are rooted in nature and Ayurvedic wisdom — pure, safe, and effective for your family and the environment.',
                'topic' => 'Products & Ingredients',
                'sort_order' => 1,
                'status' => 'active',
            ],
            [
                'question' => 'What are bio-enzymes and how do they work?',
                'answer' => 'Bio-enzymes are natural cleaning agents produced by fermenting organic matter such as citrus peels, jaggery, and water. They break down dirt, grease, bacteria, and odour-causing molecules at a molecular level — leaving surfaces spotlessly clean without the need for harsh chemicals. Unlike conventional cleaners, bio-enzymes are fully biodegradable and completely safe for the environment.',
                'topic' => 'Products & Ingredients',
                'sort_order' => 2,
                'status' => 'active',
            ],
            [
                'question' => 'Are your ingredients ethically sourced?',
                'answer' => 'Yes. At CleannOrganics, we are committed to responsible and ethical sourcing. Our ingredients — from Reetha (soapnut) and neem to coconut coir, bamboo, and copper — are sourced from trusted natural suppliers across India. We believe that what goes into our products should be as clean and honest as the products themselves.',
                'topic' => 'Products & Ingredients',
                'sort_order' => 3,
                'status' => 'active',
            ],
            [
                'question' => 'Do your products contain parabens, sulfates, or harmful chemicals?',
                'answer' => 'Never. CleannOrganics products are completely free from parabens, sulfates, synthetic dyes, artificial fragrances, bleach, and other harmful chemicals. We are committed to creating products that are genuinely safe for your home, your family, and the planet — without any compromise whatsoever.',
                'topic' => 'Products & Ingredients',
                'sort_order' => 4,
                'status' => 'active',
            ],
            [
                'question' => 'Are CleannOrganics products suitable for sensitive skin?',
                'answer' => 'Yes! Our products are formulated with gentle, plant-based ingredients that are kind to sensitive skin. Products like our bamboo toothbrushes, coconut coir scrubbers, and sponge gourd loofah are suitable for all skin types. If you have a specific skin condition or allergy, we recommend checking the ingredient list or contacting us on WhatsApp at +91 9999667014 before use.',
                'topic' => 'Products & Ingredients',
                'sort_order' => 5,
                'status' => 'active',
            ],

            // Orders & Payment
            [
                'question' => 'What payment methods do you accept?',
                'answer' => 'We accept a wide range of payment methods to make your shopping experience smooth and convenient — including Credit Cards, Debit Cards, UPI (Google Pay, PhonePe, Paytm), Net Banking, and Cash on Delivery (COD). All online transactions are secured with industry-standard encryption for your complete safety.',
                'topic' => 'Orders & Payment',
                'sort_order' => 6,
                'status' => 'active',
            ],
            [
                'question' => 'Is Cash on Delivery (COD) available?',
                'answer' => 'Yes! Cash on Delivery is available on eligible orders across India. Simply select the COD option at checkout. Please note that COD availability may vary based on your delivery location and order value. For any queries, feel free to reach us on WhatsApp at +91 9999667014.',
                'topic' => 'Orders & Payment',
                'sort_order' => 7,
                'status' => 'active',
            ],
            [
                'question' => 'How do I place an order on CleannOrganics?',
                'answer' => 'Placing an order is quick and easy! Browse our products, select the variant and quantity you need, and add items to your cart. Proceed to checkout, enter your delivery details, choose your preferred payment method, and confirm your order. You will receive an order confirmation shortly after.',
                'topic' => 'Orders & Payment',
                'sort_order' => 8,
                'status' => 'active',
            ],
            [
                'question' => 'Can I modify or cancel my order after placing it?',
                'answer' => 'We process orders quickly to ensure fast delivery. If you need to modify or cancel your order, please contact us immediately on WhatsApp at +91 9999667014. We will do our best to accommodate your request; however, modifications or cancellations may not be possible once the order has been dispatched.',
                'topic' => 'Orders & Payment',
                'sort_order' => 9,
                'status' => 'active',
            ],
            [
                'question' => 'Will I receive an order confirmation after placing my order?',
                'answer' => 'Yes! Once your order is successfully placed, you will receive a confirmation notification with your order number, product details, and estimated delivery date. If you do not receive a confirmation within a few minutes, please check your spam folder or contact our team on WhatsApp at +91 9999667014.',
                'topic' => 'Orders & Payment',
                'sort_order' => 10,
                'status' => 'active',
            ],

            // Shipping & Delivery
            [
                'question' => 'How long does delivery take?',
                'answer' => 'We deliver across India with the following timelines: Metro cities (Delhi NCR, Mumbai, Bangalore, Chennai, Kolkata, Hyderabad) — 2 to 3 business days. Rest of India — 5 to 7 business days. Delivery timelines may vary slightly during peak seasons or festive periods.',
                'topic' => 'Shipping & Delivery',
                'sort_order' => 11,
                'status' => 'active',
            ],
            [
                'question' => 'Do you offer free shipping?',
                'answer' => 'Yes! Enjoy FREE shipping on all orders above ₹300. For orders below ₹300, a nominal shipping charge will be applied at checkout. We recommend adding a few more items to your cart to unlock free delivery and save on shipping costs!',
                'topic' => 'Shipping & Delivery',
                'sort_order' => 12,
                'status' => 'active',
            ],
            [
                'question' => 'Do you ship all over India?',
                'answer' => 'Yes, CleannOrganics proudly delivers pan-India — covering all major cities, towns, and remote locations across all states and union territories of India. Wherever you are, we will bring nature right to your doorstep.',
                'topic' => 'Shipping & Delivery',
                'sort_order' => 13,
                'status' => 'active',
            ],
            [
                'question' => 'How can I track my order?',
                'answer' => "Once your order is dispatched, you will receive a tracking number and a link to monitor your delivery in real time. You can use this to track your package directly on our courier partner's website. For any tracking queries, please reach us on WhatsApp at +91 9999667014.",
                'topic' => 'Shipping & Delivery',
                'sort_order' => 14,
                'status' => 'active',
            ],
            [
                'question' => 'What if my order is delayed beyond the expected date?',
                'answer' => 'While we always strive to deliver within the promised timeline, occasional delays may occur due to weather conditions, logistics challenges, or high demand during festive seasons. If your order is significantly delayed, please contact us on WhatsApp at +91 9999667014 and we will resolve it for you promptly.',
                'topic' => 'Shipping & Delivery',
                'sort_order' => 15,
                'status' => 'active',
            ],

            // Returns & Refunds
            [
                'question' => 'What is your return policy?',
                'answer' => 'We offer a hassle-free 4-day return window on eligible products from the date of delivery. If you are not satisfied with your purchase or receive a damaged or incorrect product, please contact us within 4 days of delivery on WhatsApp at +91 9999667014 to initiate a return.',
                'topic' => 'Returns & Refunds',
                'sort_order' => 16,
                'status' => 'active',
            ],
            [
                'question' => 'Which products are eligible for return?',
                'answer' => 'The following products are eligible for return within 4 days of delivery: Neem Combs, Bamboo Dish Brush, 100% Organic Earbuds, Stainless Steel Straw, Reusable Stainless Steel Travel Cup, and Copper Travel Bottle. Liquid products (floor cleaners, mosquito repellent) and personal care consumables (toothbrushes, manjan, loofah) are non-returnable for hygiene and safety reasons.',
                'topic' => 'Returns & Refunds',
                'sort_order' => 17,
                'status' => 'active',
            ],
            [
                'question' => 'How do I initiate a return?',
                'answer' => 'To initiate a return, please contact us on WhatsApp at +91 9999667014 within 4 days of receiving your order. Share your order number, the product you wish to return, and the reason for your return. Our support team will guide you through the process and arrange a pickup at your convenience.',
                'topic' => 'Returns & Refunds',
                'sort_order' => 18,
                'status' => 'active',
            ],
            [
                'question' => 'When will I receive my refund after a return?',
                'answer' => 'Once we receive and inspect the returned product, your refund will be processed within 5 to 7 business days. Refunds are credited to the original payment method. For COD orders, refunds will be processed via bank transfer or UPI. Please contact us on WhatsApp at +91 9999667014 for any refund queries.',
                'topic' => 'Returns & Refunds',
                'sort_order' => 19,
                'status' => 'active',
            ],
            [
                'question' => 'What if I receive a damaged or incorrect product?',
                'answer' => 'We sincerely apologise if you receive a damaged or incorrect product. Please contact us immediately on WhatsApp at +91 9999667014 with your order number and a clear photo of the product received. We will arrange a replacement or full refund at no additional cost to you — no questions asked.',
                'topic' => 'Returns & Refunds',
                'sort_order' => 20,
                'status' => 'active',
            ],

            // Offers & Discounts
            [
                'question' => 'Do you have any ongoing offers or discount codes?',
                'answer' => 'Yes! CleannOrganics regularly runs exciting offers, seasonal promotions, and special discount codes for our valued customers. To stay updated on the latest deals, follow us on our social media channels, subscribe to our newsletter, or save our WhatsApp number +91 9999667014 to receive exclusive offers directly.',
                'topic' => 'Offers & Discounts',
                'sort_order' => 21,
                'status' => 'active',
            ],
            [
                'question' => 'How do I apply a promo code at checkout?',
                'answer' => "Applying a discount code is simple! Add your desired products to the cart and proceed to checkout. You will find a 'Promo Code' or 'Coupon Code' field on the checkout page. Enter your code and click Apply — your discount will be deducted instantly from your order total before payment.",
                'topic' => 'Offers & Discounts',
                'sort_order' => 22,
                'status' => 'active',
            ],
            [
                'question' => 'Can I use multiple discount codes on a single order?',
                'answer' => 'Currently, only one discount or promo code can be applied per order. We recommend using the code that gives you the best saving on your cart. If you are unsure which code to use, feel free to reach us on WhatsApp at +91 9999667014 and we will help you find the best deal.',
                'topic' => 'Offers & Discounts',
                'sort_order' => 23,
                'status' => 'active',
            ],
            [
                'question' => 'Do you offer discounts on bulk orders?',
                'answer' => 'Yes! We offer special tiered pricing and discounts on bulk purchases. The more you buy, the more you save — our pricing structure automatically applies bulk discounts at checkout. For large bulk order enquiries or custom pricing for businesses, please contact us directly on WhatsApp at +91 9999667014.',
                'topic' => 'Offers & Discounts',
                'sort_order' => 24,
                'status' => 'active',
            ],
            [
                'question' => 'How can I stay updated on new offers and promotions?',
                'answer' => 'The best way to never miss a deal is to subscribe to our newsletter on the website, follow CleannOrganics on social media, and save our WhatsApp number +91 9999667014. We share exclusive discount codes, early product launches, and seasonal promotions directly with our community members.',
                'topic' => 'Offers & Discounts',
                'sort_order' => 25,
                'status' => 'active',
            ],

            // Sustainability & Eco Values
            [
                'question' => 'What makes CleannOrganics an eco-friendly brand?',
                'answer' => 'At CleannOrganics, sustainability is not just a label — it is our core purpose. Every product we create is plant-based, biodegradable, plastic-free, or crafted from sustainable natural materials like bamboo, coconut coir, copper, and sponge gourd. We are committed to reducing chemical pollution, minimising plastic waste, and making eco-conscious living accessible and affordable for every Indian household.',
                'topic' => 'Sustainability & Eco Values',
                'sort_order' => 26,
                'status' => 'active',
            ],
            [
                'question' => 'Is your packaging eco-friendly and sustainable?',
                'answer' => 'Yes, we are committed to using minimal and environmentally responsible packaging across our product range. We continuously work to eliminate single-use plastic from our packaging and replace it with recyclable, biodegradable, or reusable alternatives. Our goal is to move towards fully zero-waste packaging in the near future.',
                'topic' => 'Sustainability & Eco Values',
                'sort_order' => 27,
                'status' => 'active',
            ],
            [
                'question' => 'Are your products biodegradable?',
                'answer' => 'Most of our products and their ingredients are fully biodegradable. Our bio-enzyme cleaners break down naturally without leaving harmful residues in soil or water. Bamboo, coconut coir, sponge gourd, and organic cotton products compost naturally at the end of their life. Our stainless steel and copper products are built to last for years — reducing waste through longevity and durability.',
                'topic' => 'Sustainability & Eco Values',
                'sort_order' => 28,
                'status' => 'active',
            ],
            [
                'question' => 'How do bio-enzyme products help protect the environment?',
                'answer' => 'Bio-enzyme products are a powerful and safe alternative to synthetic chemical cleaners. They break down organic waste naturally, produce no toxic by-products, and are completely safe for water bodies, soil, and aquatic life. By choosing bio-enzyme cleaners, you are actively reducing chemical pollution in your home and helping protect the environment with every clean.',
                'topic' => 'Sustainability & Eco Values',
                'sort_order' => 29,
                'status' => 'active',
            ],
            [
                'question' => 'Why should I switch from regular cleaning products to natural alternatives?',
                'answer' => "Conventional cleaning products often contain harsh chemicals like bleach, ammonia, and synthetic fragrances that can harm your family's health, pollute water systems, and damage the environment over time. CleannOrganics natural alternatives are equally effective — but completely safe, biodegradable, and gentle on the planet. It is a simple, everyday switch that creates a meaningful difference for your home and the world around you.",
                'topic' => 'Sustainability & Eco Values',
                'sort_order' => 30,
                'status' => 'active',
            ],

            // How to Use Products
            [
                'question' => 'How do I use the Bio-enzyme Floor Cleaners?',
                'answer' => 'Using our Bio-enzyme Floor Cleaners is simple and effective. Dilute 20 to 30 ml of the cleaner in 1 litre of water. Use this solution to mop or wipe your floors as usual. No rinsing is required afterwards. For heavy stains or stubborn dirt, apply the cleaner directly on the area, leave for 2 to 3 minutes, and then mop clean. Safe for all floor types including marble, tiles, and wood.',
                'topic' => 'How to Use Products',
                'sort_order' => 31,
                'status' => 'active',
            ],
            [
                'question' => 'How do I use the Ayurvedic Manjan tooth powder?',
                'answer' => 'Take a small pinch of Ayurvedic Manjan on your fingertip or toothbrush. Gently massage it onto your teeth and gums in small circular motions for about 2 minutes. Rinse thoroughly with water afterwards. Use once or twice daily for best results. Suitable for ages 4 years and above. For children, start with an even smaller amount and supervise use.',
                'topic' => 'How to Use Products',
                'sort_order' => 32,
                'status' => 'active',
            ],
            [
                'question' => 'How do I care for bamboo products to make them last longer?',
                'answer' => 'Bamboo products last longest when kept dry between uses. After using bamboo toothbrushes, tongue cleaners, or dish brushes, shake off excess water and store them upright in a dry, well-ventilated spot. Avoid soaking bamboo products in water for extended periods, as this can cause the wood to swell, crack, or develop mould over time. Drying in sunlight occasionally also helps naturally sanitise them.',
                'topic' => 'How to Use Products',
                'sort_order' => 33,
                'status' => 'active',
            ],
            [
                'question' => 'How do I use the Copper Travel Bottle for maximum Ayurvedic benefit?',
                'answer' => 'Fill your Copper Travel Bottle with clean drinking water and allow it to rest for at least 6 to 8 hours — or overnight — before drinking. For the best Ayurvedic benefit, drink this copper-enriched water on an empty stomach first thing in the morning. Clean the bottle regularly with lemon juice and rock salt to prevent oxidation and maintain its natural shine and hygiene.',
                'topic' => 'How to Use Products',
                'sort_order' => 34,
                'status' => 'active',
            ],
            [
                'question' => 'How do I use and maintain the Coconut Coir Scrubber?',
                'answer' => 'Wet the coconut coir scrubber and apply dish soap or a natural cleaning liquid. Scrub your utensils, vessels, or kitchen surfaces in firm circular motions to remove grease and food residue. Rinse the scrubber thoroughly after each use. For the best performance and a longer lifespan, dry the scrubber in direct sunlight after every use. Sunlight also naturally sanitises it between washes.',
                'topic' => 'How to Use Products',
                'sort_order' => 35,
                'status' => 'active',
            ],

            // Child Safety
            [
                'question' => 'Are CleannOrganics products safe for children?',
                'answer' => 'Most CleannOrganics products are completely safe for the whole family, including children. Products like bamboo toothbrushes, tongue cleaners, neem combs, coconut coir scrubbers, organic earbuds, and stainless steel items are suitable for all ages with normal supervision. However, liquid products such as floor cleaners and mosquito repellents should be kept away from children below 5 years of age as a precaution.',
                'topic' => 'Child Safety',
                'sort_order' => 36,
                'status' => 'active',
            ],
            [
                'question' => 'Which products are specially designed for babies and young children?',
                'answer' => "Our Bamboo Toothbrush for Babies is specially crafted for infants and young children — featuring extra-soft bristles and a comfortable, small grip designed for tiny hands. It is BPA-free, plastic-free, and completely chemical-free. The Coconut Coir Scrubber and Sponge Gourd Bathing Loofah are also gentle and safe for children's daily bathing and skincare needs.",
                'topic' => 'Child Safety',
                'sort_order' => 37,
                'status' => 'active',
            ],
            [
                'question' => 'Is the Bio-enzyme Floor Cleaner safe if a child touches a freshly mopped floor?',
                'answer' => 'Yes. Our Bio-enzyme Floor Cleaners are made entirely from plant-based, natural ingredients with no toxic chemicals, synthetic dyes, or harmful substances. Brief skin contact with a freshly mopped floor is not harmful. However, as a standard precaution, we always recommend keeping children away from wet floors while mopping and storing all cleaning products out of the reach of children below 5 years.',
                'topic' => 'Child Safety',
                'sort_order' => 38,
                'status' => 'active',
            ],
            [
                'question' => 'Can children use the Ayurvedic Manjan tooth powder?',
                'answer' => "Our Ayurvedic Manjan is recommended for children aged 4 years and above. It is completely free from synthetic fluoride and harsh chemicals, making it a safe and natural oral care option for older children. For younger toddlers or infants, we recommend our Bamboo Toothbrush for Babies with an appropriate children's toothpaste. If you have any concerns, consult your child's dentist before use.",
                'topic' => 'Child Safety',
                'sort_order' => 39,
                'status' => 'active',
            ],
            [
                'question' => 'Which products should be kept away from young children?',
                'answer' => 'As a general safety guideline, the following products should always be stored out of reach of children below 5 years of age: Bio-enzyme Floor Cleaners, Bio-Enzyme Mosquito Repellent, and 100% Organic Earbuds (due to small parts). These products are safe for household use but are designed for adult or supervised use only. All other CleannOrganics products are safe for family use with normal parental supervision.',
                'topic' => 'Child Safety',
                'sort_order' => 40,
                'status' => 'active',
            ],
        ];
    }
}

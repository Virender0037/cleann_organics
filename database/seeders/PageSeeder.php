<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Seed the 7 managed static pages.
     *
     * The first 6 pages (privacy-policy, terms-and-conditions,
     * refund-return-policy, shipping-policy, our-mission, our-story) are
     * matched by slug via updateOrCreate — inserted if missing, updated in
     * place if already present. Four of these six are placeholder content
     * and are explicitly marked as such in their body text
     * (refund-return-policy, shipping-policy, our-mission, our-story).
     *
     * The disclaimer page (existing DB id 4) is updated by id only:
     * its content and featured_image are intentionally left out of the
     * update payload so the existing body and image are preserved exactly.
     *
     * about-us, contact-us, and blog are intentionally out of scope and are
     * not referenced anywhere in this seeder.
     */
    public function run(): void
    {
        foreach ($this->pages() as $page) {
            Page::updateOrCreate(
                ['slug' => $page['slug']],
                [
                    'title' => $page['title'],
                    'content' => $page['content'],
                    'sort_order' => $page['sort_order'],
                    'status' => 'active',
                    'meta_title' => $page['meta_title'],
                    'meta_description' => $page['meta_description'],
                    'canonical_url' => $page['canonical_url'],
                ]
            );
        }

        Page::where('id', 4)->where('slug', 'disclaimer')->update([
            'sort_order' => 7,
            'status' => 'active',
            'meta_title' => 'Disclaimer | CleannOrganics',
            'canonical_url' => 'https://cleannorganics.com/page/disclaimer',
        ]);
    }

    /**
     * @return array<int, array<string, string|int>>
     */
    private function pages(): array
    {
        return [
            [
                'slug' => 'privacy-policy',
                'title' => 'Privacy Policy',
                'sort_order' => 1,
                'meta_title' => 'Privacy Policy | CleannOrganics',
                'meta_description' => "Read CleannOrganics' Privacy Policy to understand how we collect, use, and protect your personal information when you shop with us.",
                'canonical_url' => 'https://cleannorganics.com/page/privacy-policy',
                'content' => <<<'CONTENT'
Privacy Policy — CleannOrganics
Last Updated: January 2025

At CleannOrganics, we are committed to protecting your privacy and the security of your personal information. This Privacy Policy explains how we collect, use, store, and protect your data when you visit our website or place an order with us.

1. INFORMATION WE COLLECT
We collect the following information when you interact with CleannOrganics:
- Personal Information: Name, phone number, and delivery address provided at checkout.
- Order Information: Products purchased, payment method, and order history.
- Device & Usage Data: IP address, browser type, and pages visited (via cookies and analytics).

2. HOW WE USE YOUR INFORMATION
We use your information to:
- Process and fulfil your orders and arrange delivery.
- Send order confirmations, shipping updates, and delivery notifications.
- Respond to your queries and provide customer support.
- Improve our website, products, and shopping experience.
- Send promotional offers and updates (only if you have opted in).

3. SHARING YOUR INFORMATION
We do not sell or rent your personal information. We may share data with:
- Trusted delivery and logistics partners to fulfil orders.
- Payment gateway providers for secure transaction processing.
- Legal authorities if required by law.

4. DATA SECURITY
We implement industry-standard security measures to protect your personal data from unauthorised access or misuse. All online payments are encrypted and processed securely.

5. COOKIES
Our website uses cookies to enhance your browsing experience and analyse website traffic. You may manage cookie preferences through your browser settings.

6. YOUR RIGHTS
You have the right to access, update, or request deletion of your personal data at any time. Please contact us on WhatsApp at +91 9999667014 to exercise these rights.

7. CHANGES TO THIS POLICY
We may update this Privacy Policy periodically. Any changes will be reflected on this page with an updated date.

For privacy-related queries, contact us on WhatsApp at +91 9999667014.
CONTENT,
            ],
            [
                'slug' => 'terms-and-conditions',
                'title' => 'Terms & Conditions',
                'sort_order' => 2,
                'meta_title' => 'Terms & Conditions | CleannOrganics',
                'meta_description' => "Read CleannOrganics' Terms & Conditions before using our website or placing an order. Governs purchases, cancellations, and use of our platform.",
                'canonical_url' => 'https://cleannorganics.com/page/terms-and-conditions',
                'content' => <<<'CONTENT'
Terms & Conditions — CleannOrganics
Last Updated: January 2025

Welcome to CleannOrganics. By accessing our website and placing orders with us, you agree to the following Terms & Conditions. Please read them carefully before using our website or making a purchase.

1. GENERAL
These Terms & Conditions govern your use of cleannorganics.com. By using our website, you confirm that you are at least 18 years of age or accessing the website under parental supervision.

2. PRODUCTS & AVAILABILITY
All products are subject to availability. We reserve the right to discontinue or modify any product without prior notice. Product images are for illustrative purposes only and may vary slightly from the actual product.

3. PRICING & PAYMENT
All prices are listed in Indian Rupees (INR) and are inclusive of applicable taxes (GST). We reserve the right to change prices without prior notice. Payment must be made in full at the time of placing the order (except COD orders).

4. ORDERS & CANCELLATIONS
Orders enter our processing system immediately upon placement. Cancellations or modifications may not be possible once an order has been dispatched. To request a cancellation, please contact us immediately on WhatsApp at +91 9999667014.

5. SHIPPING & DELIVERY
We deliver pan-India. Delivery timelines and charges are as outlined in our Shipping Policy. CleannOrganics is not responsible for delays caused by courier partners or unforeseen circumstances.

6. RETURNS & REFUNDS
Our return and refund process is governed by our Refund & Return Policy. Returns are accepted within 4 days of delivery for eligible products only.

7. INTELLECTUAL PROPERTY
All website content — including text, images, logos, and product descriptions — is the intellectual property of CleannOrganics and may not be copied or used without prior written permission.

8. LIMITATION OF LIABILITY
CleannOrganics shall not be liable for any indirect or consequential damages arising from the use of our products or website. Our maximum liability shall not exceed the value of the order placed.

9. GOVERNING LAW
These Terms & Conditions are governed by the laws of India. Any disputes shall be subject to the exclusive jurisdiction of the courts in India.

For queries, contact us on WhatsApp at +91 9999667014.
CONTENT,
            ],
            [
                'slug' => 'refund-return-policy',
                'title' => 'Refund & Return Policy',
                'sort_order' => 3,
                'meta_title' => 'Refund & Return Policy | CleannOrganics',
                'meta_description' => 'Read the CleannOrganics Refund & Return Policy for information about returns, refunds, replacements, and order cancellations.',
                'canonical_url' => 'https://cleannorganics.com/page/refund-return-policy',
                'content' => <<<'CONTENT'
Refund & Return Policy — CleannOrganics
Last Updated: January 2025
[PLACEHOLDER CONTENT — REPLACE BEFORE PRODUCTION]

At CleannOrganics, customer satisfaction is important to us. This Refund & Return Policy explains the general process for requesting a return, replacement, or refund for products purchased through our website.

1. RETURN ELIGIBILITY
Customers may request a return for eligible products within the applicable return period after delivery.

Products should be unused, unopened, and returned in their original packaging unless the product was received damaged, defective, or incorrect.

2. DAMAGED OR INCORRECT PRODUCTS
If you receive a damaged, defective, or incorrect product, please contact CleannOrganics customer support with your order details and supporting photographs.

Our team will review the request and provide further instructions.

3. NON-RETURNABLE PRODUCTS
Certain products may not be eligible for return due to hygiene, safety, or other applicable conditions.

The eligibility of a product for return will be determined according to the applicable product and order conditions.

4. RETURN PROCESS
To request a return, contact us with your order number and reason for the return.

Once the request is reviewed and approved, our team will provide instructions regarding the next steps.

5. REFUNDS
Approved refunds will be processed using the applicable refund method after the returned product has been received and verified.

The time required for the refunded amount to reflect may vary depending on the payment method or financial institution.

6. CANCELLATIONS
Order cancellation requests should be submitted as soon as possible.

Orders that have already been processed or dispatched may not be eligible for cancellation.

7. CONTACT US
For questions regarding returns, refunds, replacements, or cancellations, please contact CleannOrganics customer support.

[END OF PLACEHOLDER CONTENT]
CONTENT,
            ],
            [
                'slug' => 'shipping-policy',
                'title' => 'Shipping Policy',
                'sort_order' => 4,
                'meta_title' => 'Shipping Policy | CleannOrganics',
                'meta_description' => 'Read the CleannOrganics Shipping Policy for information about order processing, shipping charges, delivery timelines, and order tracking.',
                'canonical_url' => 'https://cleannorganics.com/page/shipping-policy',
                'content' => <<<'CONTENT'
Shipping Policy — CleannOrganics
Last Updated: January 2025
[PLACEHOLDER CONTENT — REPLACE BEFORE PRODUCTION]

At CleannOrganics, we aim to process and deliver your orders safely and efficiently. This Shipping Policy provides general information regarding order processing, shipping, and delivery.

1. SHIPPING COVERAGE
CleannOrganics ships products to eligible locations across India.

Availability of delivery may depend on the customer's location, PIN code, courier service availability, and other logistical factors.

2. ORDER PROCESSING
Orders are processed after successful order confirmation.

Processing times may vary depending on product availability, order volume, holidays, and other operational factors.

3. SHIPPING CHARGES
Applicable shipping charges, if any, will be displayed during checkout before the order is confirmed.

Shipping charges may vary depending on the delivery location, order value, product weight, and applicable shipping method.

4. DELIVERY TIME
Estimated delivery timelines may vary based on the customer's location and courier partner.

Any delivery timeline displayed on the website should be considered an estimate and may be affected by circumstances outside our control.

5. ORDER TRACKING
Where tracking information is available, customers may receive tracking details after their order has been dispatched.

6. DELIVERY DELAYS
Delivery may occasionally be delayed due to weather conditions, courier delays, public holidays, operational issues, or other unforeseen circumstances.

CleannOrganics will make reasonable efforts to assist customers with delivery-related concerns.

7. INCORRECT DELIVERY INFORMATION
Customers are responsible for providing complete and accurate shipping information when placing an order.

Incorrect or incomplete delivery details may result in delays or failed delivery attempts.

8. CONTACT US
For shipping or delivery-related queries, please contact CleannOrganics customer support.

[END OF PLACEHOLDER CONTENT]
CONTENT,
            ],
            [
                'slug' => 'our-mission',
                'title' => 'Our Mission',
                'sort_order' => 5,
                'meta_title' => 'Our Mission | CleannOrganics',
                'meta_description' => 'Learn about the CleannOrganics mission and our commitment to quality products, transparency, customer trust, and thoughtful everyday choices.',
                'canonical_url' => 'https://cleannorganics.com/page/our-mission',
                'content' => <<<'CONTENT'
Our Mission — CleannOrganics

[PLACEHOLDER CONTENT — REPLACE BEFORE PRODUCTION]

At CleannOrganics, our mission is to make clean, thoughtfully selected products accessible to customers who care about what they bring into their homes and everyday lives.

We believe that making better choices should be simple, transparent, and convenient.

OUR PURPOSE

Our purpose is to build a trusted destination where customers can discover products selected with quality, usefulness, and responsible choices in mind.

We aim to make the shopping experience straightforward while providing customers with clear information about the products they choose.

WHAT WE BELIEVE

We believe in:

- Quality products that provide genuine value.
- Transparency in the way products are presented.
- A simple and reliable shopping experience.
- Building long-term trust with our customers.
- Continuously improving our products and services.

OUR COMMITMENT

We are committed to continuously improving the CleannOrganics experience by listening to our customers, improving our product selection, and making our platform easier to use.

As CleannOrganics grows, our mission remains focused on creating a reliable destination for customers looking to make thoughtful everyday choices.

[END OF PLACEHOLDER CONTENT]
CONTENT,
            ],
            [
                'slug' => 'our-story',
                'title' => 'Our Story',
                'sort_order' => 6,
                'meta_title' => 'Our Story | CleannOrganics',
                'meta_description' => 'Learn about the story behind CleannOrganics, our journey, what we are building, and our commitment to creating a simple, transparent, and reliable shopping experience.',
                'canonical_url' => 'https://cleannorganics.com/page/our-story',
                'content' => <<<'CONTENT'
Our Story – CleannOrganics
[PLACEHOLDER CONTENT – REPLACE BEFORE PRODUCTION]

CleannOrganics was created with the goal of bringing carefully selected products together in one place.

Our focus is on creating a shopping experience where customers can explore products, understand their options, and make informed purchasing decisions with confidence.

HOW IT STARTED

CleannOrganics started with a simple idea: make everyday shopping easier, more transparent, and more convenient.

We wanted to create a platform where customers could discover useful products while having clear access to product information, pricing, and purchasing options.

WHAT WE ARE BUILDING

We are building CleannOrganics as more than just an online store.

Our aim is to create a trusted platform where product quality, transparency, convenience, and customer experience remain at the centre of everything we do.

OUR JOURNEY

Our journey is focused on continuously improving the CleannOrganics shopping experience.

As the platform grows, we plan to expand our product selection, improve the way customers discover and evaluate products, and continue making the overall shopping experience simple and reliable.

We are committed to learning from customer feedback and improving the platform as we grow.

[END OF PLACEHOLDER CONTENT]
CONTENT,
            ],
        ];
    }
}

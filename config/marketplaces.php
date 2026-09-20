<?php

/**
 * The single source of truth for marketplace price comparison (Admin → Catalog → Products → Edit → Marketplace Pricing).
 *
 * Add a marketplace under `list` and it appears everywhere (admin dropdown, storefront cards, dialog); no migration needed.
 *
 * - `enabled`  : false hides it from the admin "Add" dropdown and from customers.
 * - `domains`  : hosts a *product URL* for that marketplace must belong to (subdomains allowed).
 *                The affiliate URL may be any https URL, because affiliate networks use their own tracking domains.
 * - Logos      : drop `public/images/marketplaces/{key}.svg|png|webp|jpg` in place and it is used automatically;
 *                otherwise a neutral text wordmark is drawn. Never hotlinked.
 */
return [
    'list' => [
        'amazon' => ['label' => 'Amazon', 'enabled' => true, 'domains' => ['amazon.in', 'amazon.com', 'amzn.in', 'amzn.to', 'a.co']],
        'flipkart' => ['label' => 'Flipkart', 'enabled' => true, 'domains' => ['flipkart.com', 'fkrt.it', 'fkrt.cc']],
        'meesho' => ['label' => 'Meesho', 'enabled' => true, 'domains' => ['meesho.com']],
        'blinkit' => ['label' => 'Blinkit', 'enabled' => true, 'domains' => ['blinkit.com']],
        'zepto' => ['label' => 'Zepto', 'enabled' => true, 'domains' => ['zeptonow.com', 'zepto.com']],
        'jiomart' => ['label' => 'JioMart', 'enabled' => true, 'domains' => ['jiomart.com']],
        'bigbasket' => ['label' => 'BigBasket', 'enabled' => true, 'domains' => ['bigbasket.com']],
        'myntra' => ['label' => 'Myntra', 'enabled' => true, 'domains' => ['myntra.com']],
        'tatacliq' => ['label' => 'Tata CLiQ', 'enabled' => true, 'domains' => ['tatacliq.com']],
    ],

    // Where a click on a marketplace link may come from (validated server-side; anything else is stored as "product_card").
    'click_sources' => ['product_detail', 'shop', 'homepage', 'related_products', 'product_card'],

    // Admin shows a "price may be outdated" warning after this many days since last_checked_at.
    'stale_after_days' => 30,
];

# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project overview

Cleann Organics is a Laravel 12 e-commerce application (storefront + admin back-office) using Blade views, Tailwind CSS, and Vite. Auth scaffolding comes from Laravel Breeze; roles/permissions use `spatie/laravel-permission`; social login uses `laravel/socialite`; API tokens use `laravel/sanctum`.

## Commands

Local dev (PHP server + queue worker + log tailer + Vite, all concurrently):
```
composer dev
```

Run the full test suite (clears config cache first):
```
composer test
# or directly:
php artisan test
```

Run a single test file / filter by name:
```
php artisan test tests/Feature/Auth/AuthenticationTest.php
php artisan test --filter=test_users_can_authenticate
```

Frontend asset build:
```
npm run dev     # Vite dev server
npm run build    # production build
```

Other common Artisan commands:
```
php artisan migrate            # run migrations (SQLite by default in tests/CI; MySQL locally per .env)
php artisan migrate:fresh --seed
php artisan tinker
```

Lint/format PHP (Laravel Pint, config default — no custom `pint.json`):
```
vendor/bin/pint
```

There is no JS linter configured (no ESLint/Prettier config present).

## Architecture

**Storefront and admin are both routed through `routes/web.php`, but almost none of it is controller-backed yet.** Nearly every route — storefront pages (`/shop`, `/product-details`, `/shopping-cart`, etc.) and the entire `/admin` prefix (catalog, inventory, sales, customers, shipping, CMS, reports, administration, settings) — is a bare `Route::view(...)` or inline closure returning a Blade view with no data passed in. The only real controllers are Breeze's `app/Http/Controllers/Auth/*`, `ProfileController`, and a stray `TrialController`. When implementing a feature, expect to be adding the controller, form request, and route-model binding for a view that already exists and already has markup — not building the view from scratch.

> **This paragraph is now stale for large parts of the storefront** — checkout, cart, wishlist, orders, blog (`/bloglist`, `/singleblog/{slug}`), product/shop pages, and payments are all real, controller-backed, and tested. See "Storefront, Payments & Blog — Production Readiness Status" below for the current, verified state before assuming a given storefront route is still a bare view.

**Eloquent models exist for the full commerce domain but most have no controllers using them yet** (`app/Models`): `Product`, `ProductVariant`, `ProductVariantImage`, `ProductSpecification`, `ProductReview`, `Category`, `Tag`, `Coupon`, `Cart`/`CartItem`, `Order`/`OrderItem`, `Payment`, `Address`, `Wishlist`, `ReturnRequest`/`ReturnItem`, `ShippingZone`/`ShippingRate`, `TaxRate`, plus CMS models `Blog`/`BlogCategory`/`BlogTag`, `Page`, `Faq`, `TeamMember`, `Testimonial`, `ContactMessage`. `User` has `role`/`status` columns plus Spatie `HasRoles`. Check `database/migrations` for the authoritative schema/columns before assuming a model's shape.

**Admin views live in three related-but-distinct trees under `resources/views`:**
- `admin/` — the actual integrated admin panel wired up in `routes/web.php`, wrapped by the `<x-admin-layout>` component (`resources/views/components/admin-layout.blade.php`), which pulls in `admin/partials/header.blade.php`, `sidebar.blade.php`, `footer.blade.php`. This is where feature work happens.
- `admin-src/` — the original purchased HTML admin template source (raw HTML/SCSS/JS, not part of the Laravel build) — reference material only.
- `admin-dist/` — a partially Blade-ified conversion of the template — reference material only.

When adding a new admin page, follow the existing pattern in `admin/<module>/<entity>/{index,create,edit,show}.blade.php` and reuse `resources/views/components/admin/*` (`breadcrumb`, `page-header`, `table-card`) rather than the `admin-dist`/`admin-src` markup directly.

**Storefront layout** uses `resources/views/layouts/app.blade.php` / `guest.blade.php` (Breeze-style) alongside a separate `resources/views/components/layouts/{header,footer,app}.blade.php` set used by the public-facing pages (home, shop, blog, etc.) — check which layout a given top-level view (`home.blade.php`, `shop.blade.php`, ...) actually extends before editing shared chrome.

**Database**: `.env`/`.env.example` default to MySQL (`cleannorganics`), but CI and `composer test` run against SQLite (`database/database.sqlite`) — keep migrations/queries portable across both.

## Admin backend module status

Status below reflects this repo's actual git state (verify with `git log`/`grep -r Controller app/Http/Controllers`/`ls app/Http/Controllers/Admin` before trusting it — work may exist in other environments, e.g. Claude Code Web sessions, that was never pushed here). New controllers/requests live under an `Admin` sub-namespace — follow that convention for any remaining work.

**Done, with full CSV + XLSX import/export** (`Import`/`Export`/`Add …` actions on the list page; import screen has Download Sample Template, accepted-format help, row-level error/skip reporting; shared parsing via `app/Services/Admin/SpreadsheetImportReader.php`, shared CSV output via `app/Services/CsvExporter.php`):
- Categories (`CategoryController`) — parent/child hierarchy (`parent_id`, self-referencing, `nullOnDelete`), manual `sort_order`; delete blocked if the category has products or child categories.
- Products (`ProductController`) — category/tax-rate/tag relationships, specifications (`syncSpecifications()`, no separate "Product Specifications" module/controller).
- Product Variants (`ProductVariantController`) — tiered pricing, default-variant enforcement, stock/unit/status rules.
- Product Tags (`TagController`) — slug uniqueness, soft deletes; import creates tags only, never touches the `product_tag` pivot.
- Coupons (`CouponController`, under Sales) — `used_count` is import-excluded (always starts at 0); strict `YYYY-MM-DD` dates; percentage/fixed value caps match `StoreCouponRequest`.
- Shipping Zones (`ShippingZoneController`) — includes free-text `zone_type` field (nullable at DB level for legacy rows, required via validation); import-only duplicate detection on `name+state+city+pincode` (zone_type excluded); creates zone records only, never `ShippingRate`/`ShippingMethod`.

**Done, CRUD only** (no import/export):
- Tax Rates, Shipping Methods, Shipping Rates (child of Shipping Zones via `shipping_zone_id`), Blogs, Blog Categories, Blog Tags, Pages, FAQs, Team Members, Testimonials, Settings, Contact Messages (inbox-style: view/status/delete), Customers + Customer Addresses/Wishlists, Dashboard.
- (2026-09-20) **Hero Slides** and **Benefits Strip** (both `HomeBannerController` over the one `home_banners` table, split by `section`; `?section=hero|benefit`), **Reels** (`ReelController`), **Settings → Storefront & Offers** (`SettingController::storefront`), plus a forward-only order status action on `admin.sales.orders.show` (`SalesOrderController::updateStatus`) — see "Storefront offers, homepage & vouchers" below.

**Done, export only** (no import, no manual create — records originate elsewhere): Product Reviews, Inventory (stock levels/low-stock/out-of-stock), Sales Orders, Returns. Sales Payments is export-only for record creation, but (2026-09-12) its detail page (`admin.sales.payments.show`) now also has a real Verify/Reject action for `manual_upi` payments — see "Storefront, Payments & Blog" below.

**Pending** — still bare `Route::view(...)`, no controller:
- Reports (sales/orders/products/inventory/payments/coupons dashboards)
- Administration (users, roles, permissions, activity logs)

Update this list as work lands — don't rely on chat history to track this.

## Storefront, Payments & Blog — Production Readiness Status (UAT phase, updated 2026-09-12)

Verified, tested state as of this phase — supersedes any earlier assumption that storefront routes are still bare `Route::view(...)` for the areas covered here (checkout, cart, wishlist, orders, blog, product/shop pages, payments are all real, controller-backed, and tested).

**Automated tests**: 593 passing, 0 failing as of 2026-09-20 (`php artisan test`; was 543 on 2026-09-12). Includes new coverage added this phase for Razorpay payments, the webhook, blog, image-fallback behavior, admin wishlist scoping, and (2026-09-12) the Manual UPI submit/verify/reject/resubmit lifecycle and private-disk proof storage/access-control.

**COD** — fully functional, unchanged, works independently of the other two payment methods. Order placed with `payment_status: pending`; admin marks paid at fulfillment.

**Manual UPI** (`manual_upi`) — real, working, admin-verified manual payment method, added 2026-09-12. Customer picks it at checkout → order placed `payment_status: pending` → redirected to `/orders/{order}/pay-upi` showing a QR code + the admin-configured UPI ID (`Setting::cached('payment')['upi_id']`, wired up on the previously-dead Admin Settings → Payment → "Manual UPI Payments" card) and an amount built from `order.grand_total`. Customer submits a UPI transaction/UTR reference (required) and an optional payment-screenshot upload, then waits — **never auto-marked paid**. Admin reviews on the Sales → Payments detail page (`admin.sales.payments.show`) and either **Verifies** (→ `payment.status=paid`, `order.payment_status=paid`, `order.order_status` flips `pending→confirmed`, mirrors Razorpay's capture) or **Rejects** with a required note (→ `payment.status=rejected`; `order.payment_status` deliberately stays `pending`, not `failed`, since the customer can resubmit from the same pay-upi page). `payments.status` moved from a fixed DB enum to `string(20)` (migration `2026_09_12_083733_add_manual_upi_support_to_payments_table`, same enum→string rationale as the Sept-12 `payment_method` migration) to add the `rejected` value without another schema change; `orders.payment_status`/`order_status` were deliberately left untouched — the `rejected` detail lives only on the `Payment` row, keeping payment status and order/shipping status on their existing separate tracks.

Payment-proof screenshots are stored on the **private `local` disk** (`storage/app/private/manual-upi-proofs/`), never `public` — there is no direct public URL. They're served only through authorized, authenticated controller routes (`orders.manual-upi.proof` re-checks order ownership; `admin.sales.payments.proof` sits inside the `superadmin`-gated admin route group) via `ManualUpiPaymentService::streamProof()`. Uploads are validated with Laravel's `image` + `mimes:jpeg,jpg,png,webp` + `max:2048` rules (rejects anything whose actual bytes aren't a real image, regardless of extension/claimed MIME type — blocks disguised executable uploads); stored filenames are server-generated UUIDs, the client's original filename/extension is never trusted or persisted. A resubmitted screenshot deletes the previous file from disk so retries never leave orphans.

**Bank Transfer** — unchanged: a real, working manual payment method (order placed `pending`, admin marks paid manually later) with no proof-submission/verification flow of its own. The legacy `upi` value is still accepted by `PlaceOrderRequest`/`CheckoutService` for backward compatibility but is **no longer offered as a checkout option** — superseded by both Manual UPI and Razorpay-powered UPI to avoid duplicate "UPI" choices on the checkout page.

**Razorpay** — code-complete, not yet configured:
- Server-side order creation (`App\Services\Payment\RazorpayService::createOrder()`), client-side Checkout.js (`resources/views/orders/pay.blade.php`), server-side payment-signature verification, and a signed webhook (`POST /api/webhooks/razorpay`, `App\Http\Controllers\Api\RazorpayWebhookController`) subscribed to `payment.captured`/`payment.failed`.
- Idempotent capture/fail/cancel state transitions live in `App\Services\Payment\RazorpayPaymentService` — safe against duplicate webhook deliveries and repeated client callbacks (never re-processes an already-paid Payment).
- `payments`/`orders`.`payment_method` changed from a fixed DB enum to `string(20)` (migration `2026_09_12_053224_add_razorpay_support_to_payments_and_orders_tables`) so a new gateway never needs another schema migration just to add an allowed value.
- **Still requires configuration before it will work**: `RAZORPAY_KEY_ID`, `RAZORPAY_KEY_SECRET`, `RAZORPAY_WEBHOOK_SECRET` in `.env` (currently blank placeholders in both `.env` and `.env.example` — no real credentials committed). Until populated, selecting "Pay Online (Razorpay)" at checkout fails gracefully (order stays `pending`, friendly retry message) rather than erroring.

**Blog** — 100% database-driven on the public site. `Storefront\BlogController` (`/bloglist`, `/singleblog/{slug}`) replaces the old static-markup views entirely; `Blog::scopePublished()` (mirrors `Product::scopePublic()`) excludes draft/archived/future-scheduled posts. Verified: draft/archived/future posts 404 on direct slug access and don't appear in listings; unknown slugs 404; category/tag/search filters and pagination are real queries. `BlogSeeder` (idempotent, `updateOrCreate`-keyed) seeds demo content — **explicitly placeholder**, not real editorial content. The homepage's separate "Latest News" section is unrelated — a live external NewsData.io feed with its own documented emergency-only static fallback, not part of the Blog CMS.

**Cart & Wishlist** — real service-layer implementations (`CartService`, `WishlistService`): guest (session) + authenticated (DB) carts, login-merge listener, tier pricing, stock clamping, coupon application shared between cart page and checkout. Wishlist heart-toggle is AJAX (no page reload) on product cards and PDP, matching cart's existing no-reload behavior (`public/js/wishlist.js`). Admin `CustomerWishlistController::destroy` ownership-scoping gap fixed.

**Responsive/mobile fixes completed this phase**:
- CRITICAL: checkout page's Delivery Address/Payment Method/Order Review no longer disappear (`display:none`) on any viewport ≤991px.
- Product-detail add-to-cart row wraps instead of forcing horizontal scroll on phones.
- Blog card images and the blog-detail tag row no longer overflow the viewport on mobile.
- Header verified overlap-free and overflow-free at 1440/1024/768/430/375px.
- Mobile hamburger menu and mini-cart drawer no longer both stay open at once.
- `/verify-email` migrated off unbranded stock Breeze/Tailwind layout onto the site's real layout.
- Dead footer "My Account" links wired to real routes; order-history pagination switched from Laravel's default Bootstrap-5 view to the site's own branded pagination partial (`vendor.pagination.shop`).
- Two fully unreachable Breeze auth views deleted (`auth/login.blade.php`, `auth/register.blade.php` — routes always redirected to `/sign-in`/`/create-account` before rendering them).

**Image fallback** — `storage_image_url(?string $path, string $fallback): string` (`app/helpers.php`) guards every storefront image: falls back to a bundled local asset whenever the referenced file doesn't actually exist on the `public` disk (not just when the DB relation is null, the previous incomplete check). Applied across product cards, PDP gallery, shop, home, header/footer logo, cart/checkout/orders/wishlist thumbnails, and all blog images. Several fallbacks previously pointed at the external `https://placehold.co` service (a single point of failure); replaced with bundled local assets. **Admin panel still has the same unguarded pattern in ~15 views** (products, variants, blogs, team members, testimonials, customer avatars, inventory lists) — not yet fixed, lower priority (staff-facing).

**Production media storage — decision still required**: locally, `storage/app/public/variants/` doesn't exist at all even though 98 `ProductVariantImage` rows reference paths there (DB populated without its accompanying media files — a data-sync gap, not a code defect; the upload code path itself is tested and correct). Before go-live: (1) decide local-disk vs S3 for the `public` filesystem disk, (2) confirm deployment actually syncs `storage/app/public` (not just the database), (3) **run `php artisan storage:link` on the production server** — not documented anywhere in this repo, and without it every image (even freshly uploaded ones) breaks site-wide.

**Still required before go-live**:
- Real product images (re-upload/migrate — this DB's image records reference files that don't exist in this environment)
- Real blog posts (the 6 seeded ones are explicitly placeholder/demo content)
- Production email configuration (`MAIL_MAILER=log` currently — verification/password-reset emails aren't actually delivered)
- A live Razorpay test-mode payment round-trip, once real test keys are added (code/logic verified via automated tests, never an actual Razorpay sandbox call)

**Go-live checklist** (summary — see chat history for the full itemized version):
- READY: COD, Razorpay code (pending keys), Manual UPI (needs admin to set a UPI ID — see below), blog, cart/wishlist, header at all breakpoints, image-fallback protection, checkout mobile layout, account/order pages, auth pages.
- NEEDS CONFIGURATION: Razorpay env vars + webhook registration, Admin → Settings → Payment → set a real UPI ID (checkout Manual UPI otherwise shows a graceful "unavailable" message instead of a QR code), `storage:link` on production, media storage strategy.
- NEEDS REAL DATA/CONTENT: product images, blog posts.
- NEEDS MANUAL TESTING: live Razorpay sandbox payment, real email delivery, a real Manual UPI submit → admin verify/reject → resubmit round-trip (UAT steps below).
- BLOCKERS: none identified.

## Storefront offers, homepage & vouchers (added 2026-09-20)

Business rules live in **one place**: `App\Services\Storefront\StorefrontSettings` (typed reader over the admin-editable `storefront` settings group — Admin → Settings → Storefront & Offers). Cart, checkout, shipping, vouchers and the homepage all read it, so they cannot disagree.

- **Prices are tax-inclusive.** The admin/listed price is the final customer price; GST is only a *breakup* extracted backwards (`amount × rate ÷ (100 + rate)`, per line, after its share of any coupon) into `orders.tax_amount` / `order_items.tax_amount`. It is never added to `grand_total` (`CheckoutService::taxAmount()`, `includedTaxByLine()`). Cart/checkout/PDP say "Inclusive of all taxes".
- **Shipping / offers** are judged on `CheckoutService::eligibleAmount()` = subtotal − discount, before shipping. Below the free-shipping threshold (default ₹399) the existing zone/rate logic applies unchanged, falling back to the admin `flat_shipping_charge` (null/0 until configured — no amount is invented). Two offers only: ≥₹399 free shipping + gift; ≥₹999 free shipping + gift + ₹150 voucher (`StorefrontSettings::offers()`, rendered by `<x-frontend.offer-bar>` on cart and checkout).
- **₹150 voucher** = an ordinary `Coupon` (fixed, `usage_limit` 1) with `user_id` (only that customer can redeem) and `source_order_id` (UNIQUE — makes issuing idempotent at DB level). `VoucherService` issues it from an `Order::updated` hook in `AppServiceProvider` when `order_status` first becomes `delivered`, from any code path that saves the model. Validity days / min order are admin-configurable (validity defaults to 365 days, not client-specified).
- **Reviews**: only a customer with a **delivered** order containing the product, once per product (`ReviewEligibility`, enforced in `StoreProductReviewRequest`; also gates the "Write a Review" links on order pages).
- **COD wording**: customers see "Payment on Delivery" via `Order::customerPaymentLabel()`; the stored `payment_status` stays `pending`. Admin screens use the raw value. Dashboard "Total/Active Orders" link to `/order-history` and `/order-history?status=active` (`Order::ACTIVE_STATUSES`).
- **Add-to-cart toast**: `public/js/cart.js` builds it (textContent only) from the server JSON (`productName`, `addedQuantity`, `itemCount`, `cartTotal`, `cartUrl`) — the browser never computes a total. Home loads `cart.js` too.
- **Homepage sliders — one table, no second system**: `home_banners.section` = `hero` (main slideshow) or `benefit` (trust strip under it); future homepage sliders reuse it. Admin: Hero Slides / Benefits Strip (CMS). Columns beyond the basics: `icon` (built-in SVG key from `HomeBanner::ICONS`, rendered by `<x-frontend.benefit-icon>`), `alt_text`, `opens_new_tab`, `mobile_image`, `link_type` (`product|category|tag|url|none`). Images: public disk `home-banners/`, server-generated names, replaced/removed/deleted files are cleaned up. A benefit description may contain the token `{free_shipping_threshold}` (resolved by `HomeBanner::renderedSubtitle()`) so the Free Shipping card always follows the real threshold. Both sections render nothing when they have no active items. Sliders are initialised in `public/js/home1.js` (`.home-hero__slider`, `.home-benefits__slider`).
- **Seeders (production-safe, run once each)**: `HomeBenefitSeeder` (4 cards) and `HomeHeroSeeder` (3 slides; optimised WebP copies of the Clean Organics lifestyle photos live in `database/seeders/assets/home-hero/`, copied onto the public disk). Each records a flag in `settings` (`home_benefits_seeded` / `home_hero_seeded`) and seeds only into an empty section, so re-runs never duplicate or overwrite admin edits.
- **Tag-driven homepage collections**: "100% Bio-Enzyme Products" and "365 Days Lowest Price" show products carrying the Product Tag slugs `bio-enzyme` / `365-days-lowest-price` (`ProductCatalogService::TAG_*`); hidden until curated. "Sale of the Month" (formerly Hot Deals) is hidden while no product is genuinely discounted. "Explore Our Range" (Under ₹10/₹50/₹99) links to the shop's existing `max_price` filter, counts from the same price rule (`ProductCatalogService::priceBands()`).
- **Reels** (`reels` table, Admin → CMS → Reels): each ties to a real product/variant; "Add to Cart" uses the normal cart endpoint. Videos are either an upload (≤30 MB) or a URL — the original 112 MB source video has **not** been optimised/used (no `ffmpeg` locally); the section stays empty until a reel is added in admin.
- **Newsletter removed** everywhere (sections + popup). **Contact Us**: `Storefront\ContactController` saves a `ContactMessage` (validation, honeypot, `throttle:5,1`); address/email/phone come from Admin → Settings → General company fields, hidden when unset. About Us no longer uses the stock farmer photo.
- **Storefront CSS**: still no build step — this phase's styles are in `public/scss/components/_storefront-offers.scss`, mirrored verbatim at the end of `public/css/style.css` (the served file). Edit both.
- **Velocity**: checkout/order pages only show the text "Shipping Partner: Velocity" — there is still **no** Velocity integration (see below).
- **Local dev gotcha**: local `APP_URL=http://localhost` does not include the `/cleann_organics/public` subfolder, so `Storage::url()` image URLs 404 in a browser (existing product images too). Not a code bug — production `APP_URL` is correct. To eyeball locally: `APP_URL=http://127.0.0.1:8000 php artisan serve`.

**Production deploy after pulling this phase**: `php artisan migrate --force`, then `php artisan db:seed --class=HomeBenefitSeeder --force` and `--class=HomeHeroSeeder --force`, `php artisan storage:link` (if missing), `php artisan optimize:clear` (settings are cached forever via `Setting::cached()`), then config/route/view cache. Backup the DB first; roll back by restoring the backup, not `migrate:rollback` (drops banner/reel data).

## Shipping — Velocity Integration: PENDING / BLOCKED BY API DOCUMENTATION (added 2026-09-12)

**Status: on hold. No Velocity-specific code exists in this repo — none should be added until real API documentation is supplied.** The current flat-rate `ShippingZone`/`ShippingRate` checkout flow (see below) is untouched and must stay untouched until this is unblocked.

**Why it's blocked**: a repo-wide search found zero references to a "Velocity" shipping/logistics API anywhere in this codebase (the only two hits for the word are unrelated — inside the Swiper carousel and parallax-scroll third-party JS libraries). There are multiple logistics companies with similar names; guessing endpoints, auth headers, or payload shapes would mean shipping fabricated integration code, which was explicitly ruled out.

**What's needed before implementation can start** — official Velocity documentation (link, PDF, or Postman collection) covering:
- API base URL (and whether test/sandbox vs. production use different hosts)
- Authentication method (API key header, OAuth, HMAC-signed requests, etc.)
- Serviceability API (check if a PIN/postal code is deliverable)
- Shipping rate API (request/response shape, what inputs it needs — weight, dimensions, COD vs. prepaid, zone)
- Shipment/order creation API (exact payload fields expected for customer, address, order items, package)
- AWB generation (is it returned by the shipment-creation call, or a separate step?)
- Tracking API (poll-based, or webhook-only?)
- Cancellation API (when it's allowed, what it returns)
- Webhook documentation: payload shape for status updates, and their **signature/authentication verification method** — must be verified server-side before trusting any webhook payload, matching the pattern already established for the Razorpay webhook (`RazorpayWebhookController`)
- Required package weight/dimension units (kg vs. g, cm vs. inches, etc.) — relevant because `product_variants.weight` currently has no enforced unit (admin form just labels it "Weight," a code comment elsewhere *assumes* kg but nothing validates that), and no dimension (length/width/height) fields exist on `product_variants` at all yet
- COD vs. prepaid payload requirements (how the collectable COD amount is communicated, and that it must be zero for prepaid/Razorpay orders)

**Credentials**: once documentation is available, all Velocity credentials (API keys, client ID, secret, tokens, warehouse IDs, account credentials) will be supplied via `.env` by the project owner and read through a dedicated `config/services.php` (or `config/velocity.php`) entry — never hardcoded, never read via bare `env()` calls outside config files, matching the existing Razorpay pattern.

**Current shipping architecture (unchanged, for reference when this unblocks)**: `ShippingZone` (name/state/city/pincode/zone_type) has many `ShippingRate` (weight-bracket flat charges, free-shipping threshold). `CheckoutService::resolveShippingZone()`/`resolveShippingRate()`/`shippingAmount()` do a local, non-API flat-rate lookup at checkout, matched by address specificity (pincode → city → state → catch-all) then cart weight bracket. The result is frozen onto `orders.shipping_amount` + `orders.shipping_zone_name` (a string snapshot, not an FK) at order placement. `ShippingMethod` (separate model/admin CRUD) is **not connected to this flow at all** — confirmed via grep, it's referenced nowhere outside its own admin controller/requests; this is pre-existing, not something introduced by the Velocity investigation. No `shipments` table or AWB/tracking/courier columns exist on `orders` yet — those would need to be added (a dedicated `Shipment` model/table was proposed over adding many one-off columns to `orders`) once Velocity work actually starts.

**Open architectural question to resolve before implementation starts**: should Velocity *replace* the flat-rate checkout calculation with a live serviceability/rate call, or *layer in after payment* for shipment creation/tracking only while keeping the existing flat-rate charge as-is? These have different scopes and risk profiles — needs an explicit decision, not an assumption.

## Workflow: implementing a module

Whenever asked to implement a module (any item above, or a new one), **before writing any code**:

1. Read the existing Blade view(s) for that module under `resources/views/admin/<module>/...` to see what fields, tables, and actions the UI already expects.
2. Check for reusable pieces already in the codebase — `resources/views/components/admin/*` (`breadcrumb`, `page-header`, `table-card`), existing form-request classes, existing model relationships/scopes — instead of writing new ones.
3. Do not duplicate logic/markup that already exists elsewhere in the app.
4. Present the implementation plan (routes, controller methods, form requests, view changes) and get confirmation before writing code.

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

**Eloquent models exist for the full commerce domain but most have no controllers using them yet** (`app/Models`): `Product`, `ProductVariant`, `ProductVariantImage`, `ProductSpecification`, `ProductReview`, `Category`, `Tag`, `Coupon`, `Cart`/`CartItem`, `Order`/`OrderItem`, `Payment`, `Address`, `Wishlist`, `ReturnRequest`/`ReturnItem`, `ShippingZone`/`ShippingRate`, `TaxRate`, plus CMS models `Blog`/`BlogCategory`/`BlogTag`, `Page`, `Faq`, `TeamMember`, `Testimonial`, `ContactMessage`. `User` has `role`/`status` columns plus Spatie `HasRoles`. Check `database/migrations` for the authoritative schema/columns before assuming a model's shape.

**Admin views live in three related-but-distinct trees under `resources/views`:**
- `admin/` — the actual integrated admin panel wired up in `routes/web.php`, wrapped by the `<x-admin-layout>` component (`resources/views/components/admin-layout.blade.php`), which pulls in `admin/partials/header.blade.php`, `sidebar.blade.php`, `footer.blade.php`. This is where feature work happens.
- `admin-src/` — the original purchased HTML admin template source (raw HTML/SCSS/JS, not part of the Laravel build) — reference material only.
- `admin-dist/` — a partially Blade-ified conversion of the template — reference material only.

When adding a new admin page, follow the existing pattern in `admin/<module>/<entity>/{index,create,edit,show}.blade.php` and reuse `resources/views/components/admin/*` (`breadcrumb`, `page-header`, `table-card`) rather than the `admin-dist`/`admin-src` markup directly.

**Storefront layout** uses `resources/views/layouts/app.blade.php` / `guest.blade.php` (Breeze-style) alongside a separate `resources/views/components/layouts/{header,footer,app}.blade.php` set used by the public-facing pages (home, shop, blog, etc.) — check which layout a given top-level view (`home.blade.php`, `shop.blade.php`, ...) actually extends before editing shared chrome.

**Database**: `.env`/`.env.example` default to MySQL (`cleannorganics`), but CI and `composer test` run against SQLite (`database/database.sqlite`) — keep migrations/queries portable across both.

## Admin backend module status

Status below reflects this repo's actual git state (verify with `git log`/`grep -r Controller app/Http/Controllers` before trusting it — work may exist in other environments, e.g. Claude Code Web sessions, that was never pushed here).

**Done:**
- Categories (`app/Http/Controllers/Admin/CategoryController.php`, `app/Http/Requests/Admin/{Store,Update}CategoryRequest.php`) — full CRUD incl. parent/child hierarchy (`parent_id`, self-referencing, `nullOnDelete`) and manual `sort_order`; delete is blocked if the category has products or child categories. New controllers/requests live under an `Admin` sub-namespace — follow that convention for the remaining modules.

**Pending** — no controllers yet (every other `/admin` route is still a bare `Route::view(...)`); views/markup already exist under `resources/views/admin/`:

- Products
- Product Variants
- Product Reviews
- Product Tags
- Product Specifications
- Tax Rates
- Inventory
- Customers
- Sales
- Shipping
- CMS
- Reports
- Administration
- Settings
- Dashboard

Update this list (move a module out of "pending") once its controller/routes/tests actually land — don't rely on chat history to track this.

## Workflow: implementing a module

Whenever asked to implement a module (any item above, or a new one), **before writing any code**:

1. Read the existing Blade view(s) for that module under `resources/views/admin/<module>/...` to see what fields, tables, and actions the UI already expects.
2. Check for reusable pieces already in the codebase — `resources/views/components/admin/*` (`breadcrumb`, `page-header`, `table-card`), existing form-request classes, existing model relationships/scopes — instead of writing new ones.
3. Do not duplicate logic/markup that already exists elsewhere in the app.
4. Present the implementation plan (routes, controller methods, form requests, view changes) and get confirmation before writing code.

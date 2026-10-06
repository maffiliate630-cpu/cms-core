# cms-core

Shared domain library for the CMS monorepo. Published as `digitanollc/cms-core`.

**Namespace:** `CMSCore\`
**Auto-discovered via:** `CMSCore\Providers\CMSCoreServiceProvider`

---

## Monorepo dependency graph

```
cms-admin ──┐
            ├── cms-core (digitanollc/cms-core)
cms-api   ──┘
```

`cms-admin` and `cms-api` each depend on `cms-core`. There is **no dependency or shared code between cms-admin and cms-api**. Their only connection is the shared PostgreSQL database (schema owned by cms-admin) and this package.

---

## Framework support

`cms-core` requires PHP `^8.4` and resolves against **both Laravel 12 and Laravel 13** (`laravel/framework: ^12.0|^13.0`). This dual constraint is transitional: it exists only so the two consuming apps can migrate independently.

| App | Laravel |
|---|---|
| `cms-admin` | 12 |
| `cms-api` | 13 |

Third-party constraints are pinned to the lowest release of each package that declares Laravel 13 support, so a consuming app on either major resolves cleanly:

| Package | Constraint | Notes |
|---|---|---|
| `spatie/laravel-multitenancy` | `^4.2` | 4.2.0 added L13 |
| `spatie/laravel-permission` | `^6.25\|^7.2\|^8.3` | all three majors span L12 + L13 |
| `spatie/laravel-medialibrary` | `^11.23.8` | 11.23.8 added L13 |
| `laravel/sanctum` | `^4.3.3` | 4.3.3 added L13 |
| `kalnoy/nestedset` | `^6.0\|^7.0` | no single major spans both: v6 is L12-only, v7 is L13-only |

**Drop Laravel 12 once `cms-admin` is on 13**: narrow `laravel/framework` to `^13.0` and collapse the `spatie/laravel-permission` and `kalnoy/nestedset` constraints to their highest branch (`^8.3` and `^7.0`).

---

## Directory structure

```
cms-core/
├── composer.json
├── config/
│   └── cms.php                        # Published config (app type, locale)
└── src/
    ├── Providers/
    │   └── CMSCoreServiceProvider.php # Auto-discovered, boots Sanctum model override
    ├── Models/                        # Shared Eloquent models (tenant connection)
    ├── Enums/                         # Backed string enums with options()
    ├── DataSection/                   # DataSection query resolver (see QUERY_RULES.md)
    ├── Services/                      # Business logic: ApiTokenService, TenantContextService
    ├── Repositories/                  # Data access: TenantRepository, SiteRepository
    ├── Tenancy/
    │   ├── Finder/SessionTenantFinder.php
    │   └── Tasks/                     # SwitchTenantDatabaseTask, SwitchTenantDiskTask
    ├── Helpers/
    │   ├── tenancy.php                # get_tenant_table() global helper
    │   └── Path.php                   # Disk/storage path utilities
    └── Console/Commands/
        └── SwitchTenant.php           # php artisan tenant:switch
```

---

## Installation in a consuming app

Both `cms-admin` and `cms-api` consume this package from a local path (monorepo). The path repository entry in each app's `composer.json` points to the sibling directory:

**cms-admin/composer.json** and **cms-api/composer.json**:
```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../cms-core"
        }
    ],
    "require": {
        "digitanollc/cms-core": "dev-main"
    }
}
```

Composer creates a **symlink** from `vendor/digitanollc/cms-core` → `../../cms-core`, so changes to cms-core source files are immediately visible without reinstalling. There is no need to publish or push to a registry during local development.

---

## Updating cms-core in a consuming app

After making changes to cms-core (new model, new service, new migration, etc.) run from the consuming app directory:

```bash
# cms-admin
composer run update-core

# cms-api (inside Sail container)
./vendor/bin/sail composer run update-core
```

This re-resolves the path symlink, re-runs autoload dump, and re-triggers package discovery so any new service providers, commands, or config files are picked up.

> **When to run it:** Any time you add a new class, change a namespace, add a new `CMSCoreServiceProvider` registration, or modify `composer.json` in cms-core.

---

## Service provider

`CMSCoreServiceProvider` is auto-discovered and:
- Registers and merges `config/cms.php`
- Overrides Sanctum's personal access token model: `Sanctum::usePersonalAccessTokenModel(ApiToken::class)`
- Registers the `tenant:switch` Artisan command

Publish the config if you need to override it in a consuming app:
```bash
php artisan vendor:publish --tag=cms-config
```

---

## Models

All models extend `TenantModel` which sets `protected $connection = 'tenant'` — every query runs against the tenant PostgreSQL connection (schema-prefixed by `SwitchTenantDatabaseTask`).

| Model           | Table             | Key relations                                                                                                      |
|-----------------|-------------------|--------------------------------------------------------------------------------------------------------------------|
| `Blog`          | `blogs`           | `author`, `blogDetail`, `categories` (Terms), `tags` (Terms), `meta`, `products`, `images` (morph), media (Spatie) |
| `BlogDetail`    | `blog_detail`     | `blog`                                                                                                             |
| `Author`        | `authors`         | `blogs`, `meta`, media (Spatie)                                                                                    |
| `Brand`         | `brands`          | `brand_details`, `coupons`, `products`, `categories` (Terms), `meta`, `images` (morph), media (Spatie)             |
| `BrandDetail`   | `brand_details`   | `brand`                                                                                                            |
| `Coupon`        | `coupons`         | `brand`, `coupon_details`                                                                                          |
| `CouponDetail`  | `coupon_details`  | `coupon`                                                                                                           |
| `Event`         | `events`          | `category` (Term), `brands` (many-to-many via `brand_event`), `coupons` (many-to-many via `event_coupon`)          |
| `Term`          | `terms`           | `parent`, `children`, `meta` — used for all taxonomy (blog categories, brand categories, tags)                     |
| `Product`       | `products`        | `brand`                                                                                                            |
| `Menu`          | `menus`           | nested set (kalnoy/nestedset)                                                                                      |
| `Page`          | `pages`           | —                                                                                                                  |
| `Meta`          | `metas`           | polymorphic (`metaable`) — attached to Blog, Author, Brand, Term                                                   |
| `Image`         | `images`          | polymorphic (`imageable`)                                                                                          |
| `Redirection`   | `redirections`    | —                                                                                                                  |
| `Datastore`     | `datastore`       | —                                                                                                                  |
| `AffiliateLink` | `affiliate_links` | —                                                                                                                  |
| `Site`          | `sites`           | landlord table (`public` schema), `details`                                                                        |
| `Tenant`        | `tenants`         | landlord table, `HasTenants` trait for user–tenant relationship                                                    |
| `ApiToken`      | `api_tokens`      | Sanctum personal access token override, `tokenable` → Tenant                                                       |

### TenantModel

```php
// All tenant-scoped models use the 'tenant' DB connection:
class TenantModel extends Model
{
    protected $connection = 'tenant';
}
```

Do not override `$connection` or `$table` in consuming apps. Use `TenantModel` as the base class for any new tenant-scoped models in cms-core.

### Media-enabled models (Spatie MediaLibrary)

`Blog`, `Brand`, and `Author` implement `HasMedia` and use `InteractsWithMedia`. They also use the `HasMediaUpload` trait, which **must exist in the consuming application** at `App\Traits\HasMediaUpload`:

- **cms-admin** — full implementation with upload/delete helpers
- **cms-api** — empty stub (read-only; `InteractsWithMedia` handles `getFirstMediaUrl()`)

If you add a new cms-core model that uses `HasMedia`, the same pattern applies.

---

## Enums

All enums are backed string enums and include a static `options()` method returning `[['value' => ..., 'label' => ...]]` for use in select inputs.

| Enum | Values |
|---|---|
| `BlogStatus` | `draft`, `published`, `archived`, `scheduled` |
| `CategoryType` | `blog_cat`, `brand_cat`, `tag_cat` |
| `CouponStatus` | `Free Shipping`, `Best Coupon`, `Exclusive`, `Verified` |
| `HttpRedirectionStatus` | (HTTP redirect codes) |
| `MenuType` | — |

Usage:
```php
use CMSCore\Enums\BlogStatus;

// In a migration
$table->enum('status', BlogStatus::cases());

// In a query
Blog::query()->where('status', BlogStatus::PUBLISHED);

// In a Vue/Inertia select
BlogStatus::options(); // [['value' => 'published', 'label' => 'Published'], ...]
```

---

## Services

### `ApiTokenService`

Manages permanent API keys in the `api_tokens` table (Sanctum override). Used by cms-admin to create/regenerate keys and by cms-api to validate them.

```php
use CMSCore\Services\ApiTokenService;

// Generate a new permanent key for a tenant (returns plain-text — store it once)
$plainText = app(ApiTokenService::class)->generate($tenant);

// Revoke all existing keys and issue a new one
$plainText = app(ApiTokenService::class)->regenerate($tenant);
```

The plain-text token is only available at creation time. It is stored hashed and cannot be recovered. Surface it to the user immediately.

### `TenantContextService`

Base service for tenant resolution and activation. **cms-admin and cms-api each extend this** with their own `resolveTenant()` and `ensureAuthorized()` implementations.

```php
// Core API — override in app-specific subclasses:
$service->initialize(Request $request): void   // authorize + resolve + activate
$service->activateTenant(Tenant $tenant): void // calls $tenant->makeCurrent()
$service->resolveTenant(Request $request): ?Tenant
$service->resolveTenantFromId(?int $id): ?Tenant
$service->resolveTenantFromIdentifier(string $uuid): ?Tenant
```

---

## Repositories

### `TenantRepository`

```php
use CMSCore\Repositories\TenantRepository;

$repo->find(int $id): ?Tenant
$repo->findBySchema(string $schema): ?Tenant
$repo->findByIdentifier(string $uuid): ?Tenant
$repo->create(array $data): Tenant      // use TenantManagementService instead
$repo->update(Tenant, array): Tenant
$repo->delete(Tenant): bool
```

### `SiteRepository`

Wraps `Site` and `SiteDetails` creation/update in a single call.

```php
use CMSCore\Repositories\SiteRepository;

$repo->create(array $data, array $details): Site
$repo->update(Site, array $data, array $details): Site
$repo->delete(Site): ?bool
```

---

## Tenancy

This package uses `spatie/laravel-multitenancy` with **PostgreSQL schema isolation**. Each tenant has its own schema (e.g., `site_1`). The `public` schema is the landlord (shared) layer.

### How tenant switching works

When `$tenant->makeCurrent()` is called, Spatie fires the configured switch tasks in order:

1. **`SwitchTenantDatabaseTask`** — executes `SET search_path TO {schema}, public` on the `tenant` DB connection. All subsequent queries on that connection are schema-scoped automatically.
2. **`SwitchTenantDiskTask`** — reconfigures the media disk root and URL to `storage/app/public/{tenant.identifier}/` so media files are isolated per tenant.

These tasks must be registered in `config/multitenancy.php` in each consuming app:

```php
// config/multitenancy.php
'switch_tenant_tasks' => [
    CMSCore\Tenancy\Tasks\SwitchTenantDatabaseTask::class,
    CMSCore\Tenancy\Tasks\SwitchTenantDiskTask::class,
],
```

### `SessionTenantFinder`

Used by cms-admin (session-based). Returns `Tenant::current()` — the tenant that was already activated in the current request lifecycle (e.g., by middleware). Register in `config/multitenancy.php`:

```php
'tenant_finder' => CMSCore\Tenancy\Finder\SessionTenantFinder::class,
```

cms-api does **not** use `SessionTenantFinder`. It resolves tenants from the `X-Tenant` header in its own `ResolveTenant` middleware.

---

## Helpers

### `get_tenant_table(string $baseTable): string`

Global helper (autoloaded). Returns the fully-qualified `schema.table` name for the current tenant. Useful in raw queries or when you need the prefixed table name outside Eloquent.

```php
$table = get_tenant_table('blogs'); // → "site_1.blogs"
```

Throws `RuntimeException` if no tenant is currently set.

### `CMSCore\Helpers\Path`

Disk-aware path utility. Wraps `Storage::disk()` with helpers for absolute/relative conversion, URL extraction, and URL validation.

```php
use CMSCore\Helpers\Path;

$path = Path::disk('public');

$path->isURL('https://example.com/img.jpg');         // true
$path->isValidSiteURL('https://example.com/img.jpg'); // true if same host as app.url
$path->getAbsolute('images/foo.jpg');                 // /full/disk/path/images/foo.jpg
$path->getRelative('/full/disk/path/images/foo.jpg'); // images/foo.jpg
$path->extractPathFromUrl('https://app/storage/tenant-uuid/img.jpg'); // img.jpg
$path->exists('images/foo.jpg');                      // bool
$path->delete('images/foo.jpg');                      // bool
$path->getUrl('images/foo.jpg');                      // https://app/storage/images/foo.jpg
```

---

## Artisan commands

### `tenant:switch` (provided by cms-core)

Switches the tenant context for the current process. Useful for tinker sessions and one-off scripts.

```bash
php artisan tenant:switch --tenant_id=1
php artisan tenant:switch --schema=site_1
```

---

## Config

`config/cms.php` (published via `--tag=cms-config`):

```php
return [
    'default_locale' => 'en',
    'app' => [
        'type' => env('APP_TYPE'),  // 'admin' | 'api'
    ],
];
```

---

## Adding new domain logic to cms-core

When adding a new model, service, enum, or repository:

1. Create the file in `cms-core/src/` under the appropriate directory.
2. If it is a new model that uses `HasMedia`, ensure both cms-admin and cms-api have `App\Traits\HasMediaUpload` (cms-api needs only the empty stub).
3. Migrations go in **cms-admin** only (`database/migrations/tenant/` for tenant tables, `database/migrations/` for landlord tables). Never in cms-api.
4. Run `composer run update-core` in cms-admin and cms-api after any structural change.
5. If a new Artisan command or config merge is added, register it in `CMSCoreServiceProvider` and re-run `update-core`.

---

## Key constraints (never violate)

- cms-api has **zero migrations**. The entire schema is owned by cms-admin.
- Never import `App\` classes from cms-admin inside cms-core.
- Never import `App\` classes from cms-api inside cms-core.
- cms-core models use `protected $connection = 'tenant'`. Do not override this in consuming apps.
- Do not create duplicate models, repositories, or services in cms-admin or cms-api — use what is in cms-core.

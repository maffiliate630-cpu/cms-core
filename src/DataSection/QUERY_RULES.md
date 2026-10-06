# DataSection Query Resolver

Central module for resolving DataSection data queries. Lives in `cms-core`
so both `cms-admin` and `cms-api` share identical resolution logic.

---

## Directory structure

```
DataSection/
├── Contracts/
│   └── DataTypeResolverInterface.php   # Contract every type resolver must satisfy
├── Resolvers/
│   ├── AbstractTypeResolver.php        # Pinned + dynamic merge strategy (base class)
│   ├── BlogResolver.php
│   ├── CategoryResolver.php
│   ├── BlogCategoryResolver.php
│   ├── AuthorResolver.php
│   ├── BrandResolver.php
│   ├── CouponResolver.php
│   ├── EventResolver.php
│   └── ProductResolver.php
├── QueryContext.php                    # Immutable query DTO
├── DataSectionResult.php              # Immutable result DTO
├── DataSectionResolver.php            # Main entry point / dispatcher
└── QUERY_RULES.md                     # This file
```

---

## Core concepts

### QueryContext

An immutable value object that describes _one_ query to execute.  
Has four fields: `type`, `selectedIds`, `sortBy`, `limit`.

Built from a `DataSection` or a `DataSectionItem`:

```php
QueryContext::fromSection($section);                   // section-level query
QueryContext::fromItem($section, $item);               // item-level sub-query
```

Both produce the same shape, so resolvers are agnostic about the source.

### DataSectionResult

The resolved output for one `DataSection`. Contains:

| Field        | Description                                      |
|--------------|--------------------------------------------------|
| `id`         | Section ID                                       |
| `identifier` | Section identifier (e.g. `editors-picks`)        |
| `name`       | Human-readable section name                      |
| `dataType`   | `DataSectionType` value (e.g. `blog`)            |
| `isVisible`  | Visibility flag                                  |
| `data`       | `Collection` of resolved records                 |
| `columns`    | Column definitions for the admin preview table   |

Call `->toArray()` for JSON-serialisable output.

---

## Resolution strategy

### Pinned + dynamic merge

Every query follows this two-step pattern:

1. **Pinned records** — if `selectedIds` is non-empty, those records are fetched
   by ID and sorted in PHP to preserve the user-defined display order.
2. **Dynamic fill** — if `limit > count(selectedIds)` (or no limit applies),
   additional records are fetched using `sortBy`, excluding already-pinned IDs.
3. **Result** = `[...pinned, ...dynamic]`

If `limit` is null the dynamic set is unbounded. If `limit` is exactly satisfied
by pinned records, the dynamic DB query is skipped entirely.

### Type constraints

Where one table backs more than one data type, the type declares the column
filters that narrow its candidate set via `DataSectionType::queryConstraints()`.
`AbstractTypeResolver::baseQuery()` applies them to the pinned *and* the dynamic
fetch, so a pinned ID belonging to another type is silently dropped rather than
leaking into the result.

`blog_category` is the only type that needs this today: `terms` also holds brand
categories, blog tags, event categories and event tags, so every blog-category
query is pinned to `type = 'blog_cat'`. The admin record picker reads the same
method, so the picker and the resolver always agree on what exists.

### Section vs. items

A `DataSection` is resolved in one of two ways:

| Condition              | Strategy                                                                 |
|------------------------|--------------------------------------------------------------------------|
| **No items**           | Section's own `selected_ids / sort_by / limit` → single `QueryContext`  |
| **Has DataSectionItems** | Each item is an independent `QueryContext` (shares section's `data_type`). Results are merged in `sort_order`, deduplicating by record ID. |

---

## Allowed sort keys (per type)

| Type            | Allowed `sort_by` values                                    |
|-----------------|-------------------------------------------------------------|
| `blog`          | `latest`, `oldest`, `alphabetical`                          |
| `category`      | `latest`, `oldest`, `alphabetical`                          |
| `blog_category` | `latest`, `oldest`, `alphabetical`, `most_posts`            |
| `author`        | `latest`, `oldest`, `alphabetical`, `most_posts`            |
| `brand`         | `latest`, `oldest`, `alphabetical`                          |
| `coupon`        | `latest`, `oldest`, `expiring_soon`, `highest_discount`     |
| `event`         | `latest`, `oldest`, `alphabetical`                          |
| `product`       | `latest`, `oldest`, `alphabetical`, `lowest_price`, `highest_price` |

Unknown sort keys fall back to `latest` (orderByDesc created_at).

---

## Output shapes

### blog
```json
{
  "id": 1,
  "title": "Post Title",
  "slug": "post-title",
  "author": "Author Name",
  "category": "Category Name",
  "cover_image_url": "https://cdn.example.com/image.jpg",
  "created_at": "2026-01-01T00:00:00.000000Z"
}
```

### category
```json
{
  "id": 1,
  "title": "Category Title",
  "slug": "category-title",
  "type": "blog",
  "created_at": "2026-01-01T00:00:00.000000Z"
}
```

### author
```json
{
  "id": 1,
  "name": "Author Name",
  "slug": "author-name",
  "avatar": "https://cdn.example.com/avatar.jpg",
  "blogs_count": 12,
  "created_at": "2026-01-01T00:00:00.000000Z"
}
```

### brand
```json
{
  "id": 1,
  "title": "Brand Name",
  "slug": "brand-name",
  "cover_image_url": "https://cdn.example.com/logo.jpg",
  "created_at": "2026-01-01T00:00:00.000000Z"
}
```

### blog_category
Terms of type `blog_cat`. `blogs_count` is always present and drives `most_posts`.
```json
{
  "id": 1,
  "title": "Travel",
  "slug": "travel",
  "short_description": "Guides and itineraries.",
  "cover_image_url": "https://cdn.example.com/travel.jpg",
  "cover_image_alt": "Travel",
  "blogs_count": 12,
  "created_at": "2026-01-01T00:00:00.000000Z"
}
```

### coupon
```json
{
  "id": 1,
  "title": "Coupon Title",
  "slug": null,
  "discount_code": "SAVE20",
  "affiliate_url": "https://example.com/go",
  "dynamic_content": null,
  "free_shipping": false,
  "best_coupon": true,
  "exclusive": false,
  "verified": true,
  "expiry_date": "2026-06-30T00:00:00.000000Z",
  "brand": {
    "name": "Brand Name",
    "slug": "brand-name",
    "affiliate_url": "https://example.com/brand",
    "cover_image_url": "https://cdn.example.com/logo.jpg"
  },
  "created_at": "2026-01-01T00:00:00.000000Z"
}
```

### event
```json
{
  "id": 1,
  "title": "Black Friday",
  "slug": "black-friday",
  "cover_image": "https://cdn.example.com/bf.jpg",
  "category": "Seasonal",
  "created_at": "2026-01-01T00:00:00.000000Z"
}
```

### product
Covers both brand and blog products. `brand` falls back to the denormalised
`brand_title` column when the product has no brand row. `currency` is the
landlord currency the prices are in (US Dollar by default), or `null` if unset.
```json
{
  "id": 1,
  "title": "Product Title",
  "type": "brand",
  "brand": "Brand Name",
  "brand_slug": "brand-name",
  "price": "19.99",
  "discounted_price": "14.99",
  "currency": { "id": 1, "code": "USD", "symbol": "$", "name": "US Dollar" },
  "affiliate_url": "https://example.com/buy",
  "image_url": "https://cdn.example.com/product.jpg",
  "created_at": "2026-01-01T00:00:00.000000Z"
}
```

---

## Usage

### Resolve all sections for a page

```php
$resolver = app(\CMSCore\DataSection\DataSectionResolver::class);

// Visible sections only (default), keyed by identifier
$sections = $resolver->resolvePage($page);

// Include hidden sections
$sections = $resolver->resolvePage($page, visibleOnly: false);

// Access a specific section
$editsPicks = $sections['editors-picks'] ?? null;
$editsPicks?->data;       // Collection of resolved records
$editsPicks?->toArray();  // Serialisable for JSON
```

### Resolve a single section

```php
$result = $resolver->resolveSection($section);

$result->identifier; // 'editors-picks'
$result->dataType;   // 'blog'
$result->count;      // Number of resolved records (via toArray())
$result->data;       // Collection<int, array>
$result->columns;    // [['key' => 'title', 'label' => 'Title', 'type' => 'text'], ...]
```

### Resolve a raw QueryContext (low-level)

```php
use CMSCore\DataSection\QueryContext;
use CMSCore\Enums\DataSectionType;

$context = new QueryContext(
    type: DataSectionType::BLOG,
    selectedIds: [5, 2, 9],  // pinned first, in this order
    sortBy: 'latest',
    limit: 6,
);

$data = $resolver->resolveQuery($context);
// Returns: [blog#5, blog#2, blog#9, ...3 latest blogs]
```

---

## Adding a new data type

1. Add a case to `DataSectionType`:
   ```php
   case REVIEW = 'review';
   ```
2. Implement the required enum methods:
   - `getModel()` → FQCN of the Eloquent model
   - `getLabelField()` → field used as display label in pickers
   - `getImageCollection()` → Spatie media collection name, or `null`
   - `queryConstraints()` → column filters when the table backs several types (default `[]`)
   - `allowedSorts()` → array of valid sort_by strings
   - `getResolverClass()` → FQCN of the new resolver
3. Create `src/DataSection/Resolvers/ReviewResolver.php` extending `AbstractTypeResolver`.
4. Implement `modelClass()`, `applySort()`, `project()`, `previewColumns()`.
   Override `withRelations()` if eager-loads are needed, and `constraints()`
   (returning the enum's `queryConstraints()`) if the type needs narrowing.
5. Nothing else on the backend changes — `DataSectionRequest` validates against
   the enum, the admin sort map and type picker are built from it, and `cms-api`
   serves whatever the resolver returns.

The admin UI only needs a touch-up when the type wants extra picker columns:
`Page.vue` / `Items.vue` carry a badge colour map and per-type table columns
(e.g. author + category for blogs, brand + price for products).

---

## Column `type` values (for frontend rendering)

| Value    | Renderer                              |
|----------|---------------------------------------|
| `text`   | Plain text, truncated if long         |
| `image`  | `<img>` thumbnail (h-8 w-8 rounded)  |
| `date`   | Formatted as locale date string       |
| `number` | Numeric, right-aligned                |

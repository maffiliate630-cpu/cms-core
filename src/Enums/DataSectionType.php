<?php

namespace CMSCore\Enums;

enum DataSectionType: string
{
    case BLOG          = 'blog';
    case CATEGORY      = 'category';
    case BLOG_CATEGORY = 'blog_category';
    case AUTHOR        = 'author';
    case BRAND         = 'brand';
    case COUPON        = 'coupon';
    case EVENT         = 'event';
    case PRODUCT       = 'product';

    public static function options(): array
    {
        return array_map(static fn ($case) => [
            'value' => $case->value,
            'label' => ucwords(str_replace('_', ' ', $case->value)),
        ], self::cases());
    }

    public function getModel(): string
    {
        return match ($this) {
            self::BLOG          => \CMSCore\Models\Blog::class,
            self::CATEGORY      => \CMSCore\Models\Category::class,
            self::BLOG_CATEGORY => \CMSCore\Models\Term::class,
            self::AUTHOR        => \CMSCore\Models\Author::class,
            self::BRAND         => \CMSCore\Models\Brand::class,
            self::COUPON        => \CMSCore\Models\Coupon::class,
            self::EVENT         => \CMSCore\Models\Event::class,
            self::PRODUCT       => \CMSCore\Models\Product::class,
        };
    }

    public function getLabelField(): string
    {
        return match ($this) {
            self::AUTHOR, self::BRAND => 'name',
            default => 'title',
        };
    }

    public function getImageCollection(): ?string
    {
        return match ($this) {
            self::BLOG          => 'cover_image',
            self::BLOG_CATEGORY => 'cover_image',
            self::AUTHOR        => 'images',
            self::PRODUCT       => 'image',
            default             => null,
        };
    }

    /**
     * Column constraints that always apply when querying this type's model.
     *
     * Needed where a single table backs more than one concept — `terms` holds
     * blog categories, brand categories, tags and event categories, so the
     * blog_category type must pin `type` to the matching CategoryType.
     *
     * @return array<string, string>
     */
    public function queryConstraints(): array
    {
        return match ($this) {
            self::BLOG_CATEGORY => ['type' => CategoryType::BLOG_CATEGORY->value],
            default             => [],
        };
    }

    /**
     * The fully-qualified class name of the DataTypeResolver for this type.
     * Used by DataSectionResolver to dispatch queries without a switch.
     */
    public function getResolverClass(): string
    {
        return match ($this) {
            self::BLOG          => \CMSCore\DataSection\Resolvers\BlogResolver::class,
            self::CATEGORY      => \CMSCore\DataSection\Resolvers\CategoryResolver::class,
            self::BLOG_CATEGORY => \CMSCore\DataSection\Resolvers\BlogCategoryResolver::class,
            self::AUTHOR        => \CMSCore\DataSection\Resolvers\AuthorResolver::class,
            self::BRAND         => \CMSCore\DataSection\Resolvers\BrandResolver::class,
            self::COUPON        => \CMSCore\DataSection\Resolvers\CouponResolver::class,
            self::EVENT         => \CMSCore\DataSection\Resolvers\EventResolver::class,
            self::PRODUCT       => \CMSCore\DataSection\Resolvers\ProductResolver::class,
        };
    }

    /** Allowed sort_by keys for this data type */
    public function allowedSorts(): array
    {
        return match ($this) {
            self::BLOG          => ['latest', 'oldest', 'alphabetical'],
            self::CATEGORY      => ['latest', 'oldest', 'alphabetical'],
            self::BLOG_CATEGORY => ['latest', 'oldest', 'alphabetical', 'most_posts'],
            self::AUTHOR        => ['latest', 'oldest', 'alphabetical', 'most_posts'],
            self::BRAND         => ['latest', 'oldest', 'alphabetical'],
            self::COUPON        => ['latest', 'oldest', 'expiring_soon', 'highest_discount'],
            self::EVENT         => ['latest', 'oldest', 'alphabetical'],
            self::PRODUCT       => ['latest', 'oldest', 'alphabetical', 'lowest_price', 'highest_price'],
        };
    }
}

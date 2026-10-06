<?php

namespace CMSCore\Models;

use App\Traits\HasMediaUpload;
use CMSCore\Enums\CategoryType;
use CMSCore\Models\Traits\TenantModel;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \CMSCore\Models\BlogCategory> $blogCategories
 * @property-read int|null $blog_categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \CMSCore\Models\Blog> $blogs
 * @property-read int|null $blogs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \CMSCore\Models\Brand> $brandCategories
 * @property-read int|null $brand_categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Term> $children
 * @property-read int|null $children_count
 * @property-read mixed $cover_image_url
 * @property-read \CMSCore\Models\Meta|null $meta
 * @property-read Term|null $parent
 *
 * @method static \Database\Factories\TermFactory factory($count = null, $state = [])
 * @method static Builder<static>|Term newModelQuery()
 * @method static Builder<static>|Term newQuery()
 * @method static Builder<static>|Term query()
 * @method static Builder<static>|Term whereType(\CMSCore\Enums\CategoryType|string $type)
 *
 * @mixin \Eloquent
 */
class Term extends TenantModel implements HasMedia
{
    use HasFactory, HasMediaUpload, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    public $appends = ['cover_image_url'];
    // protected $fillable = [
    //     'parent_id',
    //     'title',
    //     'slug',
    //     'short_description',
    //     'description',
    //     'type',
    // ];

    /**
     * Get the parent term (for nested terms).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Get the child terms (for nested terms).
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Get the blog categories for this term.
     */
    public function blogCategories(): HasMany
    {
        return $this->hasMany(BlogCategory::class);
    }

    /**
     * The blogs filed under this term (only meaningful for blog_cat terms).
     */
    public function blogs(): BelongsToMany
    {
        return $this->belongsToMany(Blog::class, 'blog_categories', 'category_id', 'blog_id');
    }

    public function brandCategories(): HasMany
    {
        return $this->hasMany(Brand::class, 'category_id');
    }

    /**
     * Get the meta information associated with the Term.
     */
    public function meta(): MorphOne
    {
        return $this->morphOne(Meta::class, 'metaable');
    }

    /**
     * Automatically delete related meta when category is deleted
     */
    protected static function booted(): void
    {
        static::deleting(static function ($term) {
            // Delete related meta if exists
            if ($term->meta) {
                $term->meta->delete();
            }

            // Optionally, delete child terms (if hierarchical)
            foreach ($term->children as $child) {
                $child->delete();
            }
        });
    }

    /**
     * ----------------------------------------
     * SCOPES
     * ----------------------------------------
     */

    /**
     * Scope a query to only include popular users.
     */
    #[Scope]
    protected function whereType(Builder $query, CategoryType|string $type): void
    {
        // For enum, get the value
        if ($type instanceof CategoryType) {
            $type = $type->value;
        }

        $query->where('type', '=', $type);
    }

    /**
     * Falls back to the legacy `cover_image` string column until existing
     * rows are backfilled into the media library.
     */
    protected function coverImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getFirstMediaUrl('cover_image') ?: $this->cover_image,
        );
    }
}

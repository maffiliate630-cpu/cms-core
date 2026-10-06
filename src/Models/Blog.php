<?php

namespace CMSCore\Models;

// App namespace here is resolved by having HasMediaUpload in both cms-admin & cms-api
use App\Traits\HasMediaUpload;

use CMSCore\Models\Traits\TenantModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property-read \CMSCore\Models\Author|null $author
 * @property-read \CMSCore\Models\BlogDetail|null $blogDetail
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \CMSCore\Models\Term> $categories
 * @property-read int|null $categories_count
 * @property-read mixed $cover_image_url
 * @property-read mixed $gallery_images_url
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \CMSCore\Models\Meta|null $meta
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \CMSCore\Models\Term> $tags
 * @property-read int|null $tags_count
 *
 * @method static \Database\Factories\BlogFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Blog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Blog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Blog query()
 *
 * @mixin \Eloquent
 */
class Blog extends TenantModel implements HasMedia
{
    use HasFactory,HasMediaUpload,InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    protected $casts = [
        'table_of_contents' => 'array',
        'faqs' => 'array',
    ];

    public $appends = ['cover_image_url', 'gallery_images_url'];

    /**
     * Get the author that owns the Blog.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    /**
     * Get the blogDetail associated with the Blog.
     */
    public function blogDetail(): HasOne
    {
        return $this->hasOne(BlogDetail::class);
    }

    /**
     * The categories that belong to the Blog.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Term::class, 'blog_categories', 'blog_id', 'category_id');
    }

    /**
     * The tags that belong to the Blog.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Term::class, 'blog_tags', 'blog_id', 'tag_id');
    }

    /**
     * Get the image associated with the Blog.
     */
    public function images(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    /**
     * Get the meta information associated with the Blog.
     */
    public function meta(): MorphOne
    {
        return $this->morphOne(Meta::class, 'metaable');
    }

    /**
     * Get the products associated with the Blog.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'blog_id');
    }

    protected function coverImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getFirstMediaUrl('cover_image'),
        );
    }

    protected function galleryImagesUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getMedia('gallery')
                ->map(fn ($media) => $media->getUrl()),
        );
    }
}

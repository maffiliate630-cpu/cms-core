<?php

namespace CMSCore\Models;

use App\Traits\HasMediaUpload;
use CMSCore\Models\Traits\TenantModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property-read \CMSCore\Models\Term|null $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \CMSCore\Models\Brand> $brands
 * @property-read int|null $brands_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \CMSCore\Models\Coupon> $coupons
 * @property-read int|null $coupons_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \CMSCore\Models\Product> $products
 * @property-read int|null $products_count
 * @property-read mixed $cover_image_url
 * @property-read mixed $gallery_urls
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event query()
 *
 * @mixin \Eloquent
 */
class Event extends TenantModel implements HasMedia
{
    use HasFactory;
    use HasMediaUpload;
    use InteractsWithMedia;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'cover_image',
        'gallery',
        'content',
        'category_id',
    ];

    protected $casts = [
        'gallery' => 'array',
    ];

    public $appends = ['cover_image_url', 'gallery_urls'];

    /**
     * Get the category for the event.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Term::class, 'category_id');
    }

    /**
     * The coupons that belong to the event.
     */
    public function coupons(): BelongsToMany
    {
        return $this->belongsToMany(Coupon::class, 'event_coupon');
    }

    /**
     * The brands that belong to the event.
     */
    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class, 'brand_event');
    }

    /**
     * The products featured in the event.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class)->where('type', 'event');
    }

    /**
     * The tags that belong to the event.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Term::class, 'event_tags', 'event_id', 'tag_id');
    }

    /**
     * Get the meta information associated with the Event.
     */
    public function meta(): MorphOne
    {
        return $this->morphOne(Meta::class, 'metaable');
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

    /**
     * Falls back to the legacy `gallery` json column until existing rows are
     * backfilled into the media library.
     */
    protected function galleryUrls(): Attribute
    {
        return Attribute::make(
            get: function () {
                $urls = $this->getMedia('gallery')->map(fn ($media) => $media->getUrl())->values();

                return $urls->isNotEmpty() ? $urls : collect($this->gallery ?? []);
            },
        );
    }
}

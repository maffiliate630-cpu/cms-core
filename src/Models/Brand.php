<?php

namespace CMSCore\Models;

use App\Traits\HasMediaUpload;
use CMSCore\Models\Traits\TenantModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property-read \CMSCore\Models\BrandDetail|null $brand_details
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \CMSCore\Models\Term> $categories
 * @property-read int|null $categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \CMSCore\Models\Coupon> $coupon
 * @property-read int|null $coupon_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \CMSCore\Models\Coupon> $coupons
 * @property-read int|null $coupons_count
 * @property-read mixed $cover_image_url
 * @property-read \CMSCore\Models\Image|null $images
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \CMSCore\Models\Meta|null $meta
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \CMSCore\Models\Product> $products
 * @property-read int|null $products_count
 * @property-read \CMSCore\Models\Term|null $term
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand query()
 *
 * @mixin \Eloquent
 */
class Brand extends TenantModel implements HasMedia
{
    use HasFactory,HasMediaUpload,InteractsWithMedia;

    protected $guarded = ['id'];

    protected $casts = [
        'faqs' => 'array',
    ];

    public $appends = ['cover_image_url'];

    public function term()
    {
        return $this->belongsTo(Term::class, 'category_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Term::class, 'brand_categories', 'brand_id', 'category_id');
    }

    public function coupon()
    {
        return $this->hasMany(Coupon::class);
    }

    // Alias for hasMany relationship — use plural `coupons` for conventional access
    public function coupons()
    {
        return $this->hasMany(Coupon::class);
    }

    public function brand_details()
    {
        return $this->hasOne(BrandDetail::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the meta information associated with the Blog.
     */
    public function meta(): MorphOne
    {
        return $this->morphOne(Meta::class, 'metaable');
    }

    /**
     * Get the image associated with the Brand.
     */
    public function images(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    /**
     * [Description for cover_image_url]
     */
    protected function coverImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getFirstMediaUrl('cover_image'),
        );
    }
}

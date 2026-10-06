<?php

namespace CMSCore\Models;

use App\Traits\HasMediaUpload;
use CMSCore\Models\Traits\TenantModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property-read \CMSCore\Models\Brand|null $brand
 * @property-read \CMSCore\Models\Blog|null $blog
 * @property-read \CMSCore\Models\Event|null $event
 * @property-read \CMSCore\Models\Currency|null $currency
 * @property-read mixed $image_url
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product query()
 *
 * @mixin \Eloquent
 */
class Product extends TenantModel implements HasMedia
{
    use HasFactory, HasMediaUpload, InteractsWithMedia;

    protected $guarded = ['id'];

    public $appends = ['image_url'];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function blog(): BelongsTo
    {
        return $this->belongsTo(Blog::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Resolved on the landlord connection by Currency itself. Includes deleted
     * currencies so a product keeps showing the currency it was priced in.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class)->withTrashed();
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeForBlog(Builder $query, int $blogId): Builder
    {
        return $query->where('blog_id', $blogId)->where('type', 'blog');
    }

    public function scopeForEvent(Builder $query, int $eventId): Builder
    {
        return $query->where('event_id', $eventId)->where('type', 'event');
    }

    /**
     * Falls back to the legacy `image` string column until existing rows are
     * backfilled into the media library.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getFirstMediaUrl('image') ?: $this->image,
        );
    }
}

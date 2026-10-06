<?php

namespace CMSCore\Models;

use App\Traits\HasMediaUpload;
use CMSCore\Models\Traits\TenantModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property-read mixed $avatar
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \CMSCore\Models\Blog> $blogs
 * @property-read int|null $blogs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \CMSCore\Models\Term> $categories
 * @property-read int|null $categories_count
 * @property-read string $full_title
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \CMSCore\Models\Meta|null $meta
 *
 * @method static \Database\Factories\AuthorFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Author newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Author newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Author query()
 *
 * @mixin \Eloquent
 */
class Author extends TenantModel implements HasMedia
{
    use HasFactory,HasMediaUpload,InteractsWithMedia;

    public $appends = ['avatar'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    public $fillable = ['name', 'slug', 'email', 'bio'];

    /**
     * Get all of the blogs for the Author
     */
    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class);
    }

    /**
     * The blog categories the author writes about.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Term::class, 'author_categories', 'author_id', 'category_id');
    }

    /**
     * Get the meta information associated with the author.
     */
    public function meta(): MorphOne
    {
        return $this->morphOne(Meta::class, 'metaable');
    }

    protected function avatar(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->getFirstMediaUrl('avatar'),
        );
    }

    /**
     * The author's name followed by their categories, e.g. "Jane Doe - Fashion, Beauty".
     *
     * Eager load `categories` when reading this for many authors.
     */
    protected function fullTitle(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $categories = $this->categories->pluck('title')->filter()->implode(', ');

                return $categories === '' ? (string) $this->name : "{$this->name} - {$categories}";
            },
        );
    }
}

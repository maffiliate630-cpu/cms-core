<?php

namespace CMSCore\Models;

use CMSCore\Models\Traits\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \CMSCore\Models\Blog> $blogs
 * @property-read int|null $blogs_count
 * @property-read \CMSCore\Models\Term|null $term
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogCategory query()
 *
 * @mixin \Eloquent
 */
class BlogCategory extends TenantModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];
    // protected $fillable = ['term_id'];

    /**
     * Get the term that owns the BlogCategory.
     */
    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    /**
     * Get the blogs that belong to the BlogCategory.
     */
    public function blogs(): BelongsToMany
    {
        return $this->belongsToMany(Blog::class, 'blog_categories', 'id', 'blog_id')
            ->withTimestamps();
    }
}

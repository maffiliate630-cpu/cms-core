<?php

namespace CMSCore\Models;

use CMSCore\Models\Traits\TenantModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \CMSCore\Models\Blog> $blogs
 * @property-read int|null $blogs_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 *
 * @mixin \Eloquent
 */
class Category extends TenantModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];
    // protected $fillable = ['title', 'short_description', 'description', 'slug', 'type'];

    /**
     * Get the blogs for the Category.
     */
    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class);
    }
}

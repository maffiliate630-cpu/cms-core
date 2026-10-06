<?php

namespace CMSCore\Models;

use CMSCore\Models\Traits\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read \CMSCore\Models\Blog|null $blog
 *
 * @method static \Database\Factories\BlogDetailFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BlogDetail query()
 *
 * @mixin \Eloquent
 */
class BlogDetail extends TenantModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'blog_detail';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];
    // protected $fillable = ['blog_id', 'like', 'views', 'hasAffiliate'];

    /**
     * Get the blog that owns the BlogDetail.
     */
    public function blog(): BelongsTo
    {
        return $this->belongsTo(Blog::class);
    }
}

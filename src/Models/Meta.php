<?php

namespace CMSCore\Models;

use CMSCore\Models\Traits\TenantModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property-read Model|\Eloquent $metaable
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meta query()
 *
 * @mixin \Eloquent
 */
class Meta extends TenantModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];
    // protected $fillable = [
    //     'metaable_id',
    //     'metaable_type',
    //     'title',
    //     'keywords',
    //     'description',
    // ];

    /**
     * Get the parent metaable model (morph-to).
     */
    public function metaable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Handle cleanup on delete
     * (detaches references safely without affecting the parent)
     */
    protected static function booted(): void
    {
        static::deleting(static function ($meta) {
            // Just detach safely (do not delete parent)
            $meta->metaable()->dissociate();
        });
    }
}

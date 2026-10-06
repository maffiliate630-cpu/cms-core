<?php

namespace CMSCore\Models;

use CMSCore\Models\Traits\TenantModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property-read Model|\Eloquent $imageable
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Image query()
 *
 * @mixin \Eloquent
 */
class Image extends TenantModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];
    // protected $fillable = [
    //     'file_path',
    //     'title',
    //     'alt_text',
    //     'caption',
    //     'width',
    //     'height',
    //     'imageable_id',
    //     'imageable_type',
    // ];

    /**
     * Get the parent imageable model (morph-to).
     */
    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }
}

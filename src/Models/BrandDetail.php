<?php

namespace CMSCore\Models;

use CMSCore\Models\Traits\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property-read \CMSCore\Models\Brand|null $brand
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BrandDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BrandDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BrandDetail query()
 *
 * @mixin \Eloquent
 */
class BrandDetail extends TenantModel
{
    use HasFactory;

    protected $guarded = ['id'];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}

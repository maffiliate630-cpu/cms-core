<?php

namespace CMSCore\Models;

use CMSCore\Models\Traits\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property-read \CMSCore\Models\Coupon|null $coupon
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CouponDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CouponDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CouponDetail query()
 *
 * @mixin \Eloquent
 */
class CouponDetail extends TenantModel
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => 'array',
            'uses' => 'integer',
        ];
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
}

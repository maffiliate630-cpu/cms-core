<?php

namespace CMSCore\Models;

use CMSCore\Models\Traits\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single visitor click on a coupon, captured by the public API so the admin
 * panel can report which coupons and brands actually earn traffic.
 *
 * @property int $id
 * @property int $coupon_id
 * @property int|null $brand_id
 * @property string|null $ip_address
 * @property string|null $country_code
 * @property string|null $user_agent
 * @property string|null $referrer
 * @property-read \CMSCore\Models\Coupon|null $coupon
 * @property-read \CMSCore\Models\Brand|null $brand
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CouponClick newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CouponClick newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CouponClick query()
 *
 * @mixin \Eloquent
 */
class CouponClick extends TenantModel
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'coupon_id' => 'integer',
            'brand_id' => 'integer',
        ];
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }
}

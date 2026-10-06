<?php

namespace CMSCore\Models;

use CMSCore\Models\Traits\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property-read \CMSCore\Models\Brand|null $brand
 * @property-read \CMSCore\Models\CouponDetail|null $coupon_details
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon query()
 *
 * @mixin \Eloquent
 */
class Coupon extends TenantModel
{
    use HasFactory;

    protected $fillable = [
        'title',
        'affiliate_url',
        'discount_code',
        'dynamic_content',
        'brand_id',
        'description',
        'available_date',
        'expiry_date',
        'free_shipping',
        'best_coupon',
        'exclusive',
        'verified',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
        'free_shipping' => 'boolean',
        'best_coupon' => 'boolean',
        'exclusive' => 'boolean',
        'verified' => 'boolean',
        'available_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function coupon_details()
    {
        return $this->hasOne(CouponDetail::class);
    }
}

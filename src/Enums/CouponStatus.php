<?php

namespace CMSCore\Enums;

enum CouponStatus: string
{
    case FREE_SHIPPING = 'Free Shipping';
    case BEST_COUPON = 'Best Coupon';
    case EXCLUSIVE = 'Exclusive';
    case VERIFIED = 'Verified';

    public static function options(): array
    {
        return array_map(static fn ($case) => [
            'value' => $case->value,
            'label' => $case->value,
        ], self::cases());
    }
}

<?php

namespace CMSCore\DataSection\Resolvers;

use CMSCore\Models\Coupon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CouponResolver extends AbstractTypeResolver
{
    protected function modelClass(): string
    {
        return Coupon::class;
    }

    protected function withRelations(Builder $query): Builder
    {
        return $query->with([
            'brand:id,name,slug,affiliate_url',
            'brand.media' => fn ($q) => $q->where('collection_name', 'cover_image'),
        ]);
    }

    protected function applySort(Builder $query, string $sortBy): Builder
    {
        return match ($sortBy) {
            'latest'           => $query->orderByDesc('created_at'),
            'oldest'           => $query->orderBy('created_at'),
            'alphabetical'     => $query->orderBy('title'),
            'expiring_soon'    => $query->orderBy('expiry_date'),
            // `order` is the closest numeric ranking field available on Coupon
            'highest_discount' => $query->orderByDesc('order'),
            default            => $query->orderByDesc('created_at'),
        };
    }

    protected function project(Model $record): array
    {
        /** @var Coupon $record */
        return [
            'id'              => $record->id,
            'title'           => $record->title,
            'slug'            => null,
            'discount_code'   => $record->discount_code,
            'affiliate_url'   => $record->affiliate_url,
            'dynamic_content' => $record->dynamic_content,
            'free_shipping'   => $record->free_shipping,
            'best_coupon'     => $record->best_coupon,
            'exclusive'       => $record->exclusive,
            'verified'        => $record->verified,
            'expiry_date'     => $record->expiry_date?->toISOString(),
            'brand'           => $record->brand ? [
                'name'            => $record->brand->name,
                'slug'            => $record->brand->slug,
                'affiliate_url'   => $record->brand->affiliate_url,
                'cover_image_url' => $record->brand->getFirstMediaUrl('cover_image') ?: null,
            ] : null,
            'created_at'      => $record->created_at?->toISOString(),
        ];
    }

    public function previewColumns(): array
    {
        return [
            ['key' => 'title',         'label' => 'Title',   'type' => 'text'],
            ['key' => 'discount_code', 'label' => 'Code',    'type' => 'text'],
            ['key' => 'expiry_date',   'label' => 'Expires', 'type' => 'date'],
            ['key' => 'created_at',    'label' => 'Created', 'type' => 'date'],
        ];
    }
}

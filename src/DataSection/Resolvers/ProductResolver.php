<?php

namespace CMSCore\DataSection\Resolvers;

use CMSCore\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Resolves products — both brand products and blog products, since a section
 * pins or sorts across the whole catalogue rather than one product `type`.
 */
class ProductResolver extends AbstractTypeResolver
{
    protected function modelClass(): string
    {
        return Product::class;
    }

    protected function withRelations(Builder $query): Builder
    {
        return $query->with([
            'brand:id,name,slug',
            'currency:id,name,code,symbol',
            'media' => fn ($q) => $q->where('collection_name', 'image'),
        ]);
    }

    protected function applySort(Builder $query, string $sortBy): Builder
    {
        return match ($sortBy) {
            'latest'        => $query->orderByDesc('created_at'),
            'oldest'        => $query->orderBy('created_at'),
            'alphabetical'  => $query->orderBy('title'),
            'lowest_price'  => $query->orderBy('price'),
            'highest_price' => $query->orderByDesc('price'),
            default         => $query->orderByDesc('created_at'),
        };
    }

    protected function project(Model $record): array
    {
        /** @var Product $record */
        return [
            'id'               => $record->id,
            'title'            => $record->title,
            'type'             => $record->type,
            // brand_title is the denormalised fallback for products with no brand row
            'brand'            => $record->brand->name ?? $record->brand_title,
            'brand_slug'       => $record->brand->slug ?? null,
            'price'            => $record->price,
            'discounted_price' => $record->discounted_price,
            'currency'         => $record->currency ? [
                'id'     => $record->currency->id,
                'code'   => $record->currency->code,
                'symbol' => $record->currency->symbol,
                'name'   => $record->currency->name,
            ] : null,
            'affiliate_url'    => $record->affiliate_url,
            'image_url'        => $record->image_url ?: null,
            'created_at'       => $record->created_at?->toISOString(),
        ];
    }

    public function previewColumns(): array
    {
        return [
            ['key' => 'image_url',        'label' => 'Image',   'type' => 'image'],
            ['key' => 'title',            'label' => 'Title',   'type' => 'text'],
            ['key' => 'brand',            'label' => 'Brand',   'type' => 'text'],
            ['key' => 'price',            'label' => 'Price',   'type' => 'number'],
            ['key' => 'discounted_price', 'label' => 'Sale',    'type' => 'number'],
            ['key' => 'created_at',       'label' => 'Created', 'type' => 'date'],
        ];
    }
}

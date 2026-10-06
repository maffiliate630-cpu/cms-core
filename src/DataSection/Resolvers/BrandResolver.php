<?php

namespace CMSCore\DataSection\Resolvers;

use CMSCore\Models\Brand;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BrandResolver extends AbstractTypeResolver
{
    protected function modelClass(): string
    {
        return Brand::class;
    }

    protected function withRelations(Builder $query): Builder
    {
        return $query->with([
            'media' => fn ($q) => $q->where('collection_name', 'cover_image'),
        ]);
    }

    protected function applySort(Builder $query, string $sortBy): Builder
    {
        return match ($sortBy) {
            'latest'       => $query->orderByDesc('created_at'),
            'oldest'       => $query->orderBy('created_at'),
            'alphabetical' => $query->orderBy('name'),
            default        => $query->orderByDesc('created_at'),
        };
    }

    protected function project(Model $record): array
    {
        /** @var Brand $record */
        return [
            'id'              => $record->id,
            'title'           => $record->name,
            'slug'            => $record->slug,
            'cover_image_url' => $record->getFirstMediaUrl('cover_image') ?: null,
            'created_at'      => $record->created_at?->toISOString(),
        ];
    }

    public function previewColumns(): array
    {
        return [
            ['key' => 'cover_image_url', 'label' => 'Logo',    'type' => 'image'],
            ['key' => 'title',           'label' => 'Title',   'type' => 'text'],
            ['key' => 'slug',            'label' => 'Slug',    'type' => 'text'],
            ['key' => 'created_at',      'label' => 'Created', 'type' => 'date'],
        ];
    }
}

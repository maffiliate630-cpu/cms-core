<?php

namespace CMSCore\DataSection\Resolvers;

use CMSCore\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CategoryResolver extends AbstractTypeResolver
{
    protected function modelClass(): string
    {
        return Category::class;
    }

    protected function applySort(Builder $query, string $sortBy): Builder
    {
        return match ($sortBy) {
            'latest'       => $query->orderByDesc('created_at'),
            'oldest'       => $query->orderBy('created_at'),
            'alphabetical' => $query->orderBy('title'),
            default        => $query->orderByDesc('created_at'),
        };
    }

    protected function project(Model $record): array
    {
        return [
            'id'         => $record->id,
            'title'      => $record->title,
            'slug'       => $record->slug,
            'type'       => $record->type,
            'created_at' => $record->created_at?->toISOString(),
        ];
    }

    public function previewColumns(): array
    {
        return [
            ['key' => 'title',      'label' => 'Title',   'type' => 'text'],
            ['key' => 'slug',       'label' => 'Slug',    'type' => 'text'],
            ['key' => 'type',       'label' => 'Type',    'type' => 'text'],
            ['key' => 'created_at', 'label' => 'Created', 'type' => 'date'],
        ];
    }
}

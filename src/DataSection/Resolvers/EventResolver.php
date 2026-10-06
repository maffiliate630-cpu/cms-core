<?php

namespace CMSCore\DataSection\Resolvers;

use CMSCore\Models\Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EventResolver extends AbstractTypeResolver
{
    protected function modelClass(): string
    {
        return Event::class;
    }

    protected function withRelations(Builder $query): Builder
    {
        return $query->with([
            'category:id,title',
            'media' => fn ($q) => $q->where('collection_name', 'cover_image'),
        ]);
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
        /** @var Event $record */
        return [
            'id'          => $record->id,
            'title'       => $record->title,
            'slug'        => $record->slug,
            'cover_image' => $record->cover_image_url ?: null,
            'category'    => $record->category?->title,
            'created_at'  => $record->created_at?->toISOString(),
        ];
    }

    public function previewColumns(): array
    {
        return [
            ['key' => 'title',      'label' => 'Title',    'type' => 'text'],
            ['key' => 'category',   'label' => 'Category', 'type' => 'text'],
            ['key' => 'created_at', 'label' => 'Created',  'type' => 'date'],
        ];
    }
}
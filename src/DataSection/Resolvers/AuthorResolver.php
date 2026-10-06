<?php

namespace CMSCore\DataSection\Resolvers;

use CMSCore\Models\Author;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AuthorResolver extends AbstractTypeResolver
{
    protected function modelClass(): string
    {
        return Author::class;
    }

    /**
     * Always load blogs_count so it is available in project() and for
     * the most_posts sort (which must be applied before get()).
     */
    protected function withRelations(Builder $query): Builder
    {
        return $query
            ->withCount('blogs')
            ->with(['media' => fn ($q) => $q->where('collection_name', 'images')]);
    }

    protected function applySort(Builder $query, string $sortBy): Builder
    {
        return match ($sortBy) {
            'latest'       => $query->orderByDesc('created_at'),
            'oldest'       => $query->orderBy('created_at'),
            'alphabetical' => $query->orderBy('name'),
            // blogs_count is added by withRelations() which runs before applySort()
            'most_posts'   => $query->orderByDesc('blogs_count'),
            default        => $query->orderByDesc('created_at'),
        };
    }

    protected function project(Model $record): array
    {
        /** @var Author $record */
        return [
            'id'          => $record->id,
            'name'        => $record->name,
            'slug'        => $record->slug,
            'avatar'      => $record->getFirstMediaUrl('images') ?: null,
            'blogs_count' => $record->blogs_count ?? 0,
            'created_at'  => $record->created_at?->toISOString(),
        ];
    }

    public function previewColumns(): array
    {
        return [
            ['key' => 'avatar',      'label' => 'Avatar',  'type' => 'image'],
            ['key' => 'name',        'label' => 'Name',    'type' => 'text'],
            ['key' => 'slug',        'label' => 'Slug',    'type' => 'text'],
            ['key' => 'blogs_count', 'label' => 'Posts',   'type' => 'number'],
            ['key' => 'created_at',  'label' => 'Created', 'type' => 'date'],
        ];
    }
}

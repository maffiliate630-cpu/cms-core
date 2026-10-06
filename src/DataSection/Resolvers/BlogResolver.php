<?php

namespace CMSCore\DataSection\Resolvers;

use CMSCore\Enums\BlogStatus;
use CMSCore\Models\Blog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BlogResolver extends AbstractTypeResolver
{
    protected function modelClass(): string
    {
        return Blog::class;
    }

    protected function withRelations(Builder $query): Builder
    {
        return $query->where('status', BlogStatus::PUBLISHED)->with([
            'author:id,name',
            'categories:id,title',
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
        /** @var Blog $record */
        return [
            'id'              => $record->id,
            'title'           => $record->title,
            'slug'            => $record->slug,
            'author'          => $record->author?->name,
            'category'        => $record->categories->first()?->title,
            'cover_image_url' => $record->getFirstMediaUrl('cover_image') ?: null,
            'created_at'      => $record->created_at?->toISOString(),
        ];
    }

    public function previewColumns(): array
    {
        return [
            ['key' => 'cover_image_url', 'label' => 'Cover',     'type' => 'image'],
            ['key' => 'title',           'label' => 'Title',     'type' => 'text'],
            ['key' => 'author',          'label' => 'Author',    'type' => 'text'],
            ['key' => 'category',        'label' => 'Category',  'type' => 'text'],
            ['key' => 'created_at',      'label' => 'Published', 'type' => 'date'],
        ];
    }
}

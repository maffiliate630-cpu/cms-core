<?php

namespace CMSCore\DataSection\Resolvers;

use CMSCore\Enums\DataSectionType;
use CMSCore\Models\Term;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Resolves blog categories — `terms` rows whose type is CategoryType::BLOG_CATEGORY.
 *
 * The terms table also holds brand categories, tags and event categories, so
 * every query is narrowed by the type constraint declared on the enum.
 */
class BlogCategoryResolver extends AbstractTypeResolver
{
    protected function modelClass(): string
    {
        return Term::class;
    }

    /** {@inheritDoc} */
    protected function constraints(): array
    {
        return DataSectionType::BLOG_CATEGORY->queryConstraints();
    }

    /**
     * blogs_count is always loaded so it is available in project() and for the
     * most_posts sort (which must be applied before get()).
     */
    protected function withRelations(Builder $query): Builder
    {
        return $query
            ->withCount('blogs')
            ->with(['media' => fn ($q) => $q->where('collection_name', 'cover_image')]);
    }

    protected function applySort(Builder $query, string $sortBy): Builder
    {
        return match ($sortBy) {
            'latest'       => $query->orderByDesc('created_at'),
            'oldest'       => $query->orderBy('created_at'),
            'alphabetical' => $query->orderBy('title'),
            // blogs_count is added by withRelations() which runs before applySort()
            'most_posts'   => $query->orderByDesc('blogs_count'),
            default        => $query->orderByDesc('created_at'),
        };
    }

    protected function project(Model $record): array
    {
        /** @var Term $record */
        return [
            'id'                => $record->id,
            'title'             => $record->title,
            'slug'              => $record->slug,
            'short_description' => $record->short_description,
            'cover_image_url'   => $record->cover_image_url ?: null,
            'cover_image_alt'   => $record->cover_image_alt,
            'blogs_count'       => $record->blogs_count ?? 0,
            'created_at'        => $record->created_at?->toISOString(),
        ];
    }

    public function previewColumns(): array
    {
        return [
            ['key' => 'cover_image_url', 'label' => 'Cover',   'type' => 'image'],
            ['key' => 'title',           'label' => 'Title',   'type' => 'text'],
            ['key' => 'slug',            'label' => 'Slug',    'type' => 'text'],
            ['key' => 'blogs_count',     'label' => 'Posts',   'type' => 'number'],
            ['key' => 'created_at',      'label' => 'Created', 'type' => 'date'],
        ];
    }
}

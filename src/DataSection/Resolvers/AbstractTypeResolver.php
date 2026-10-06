<?php

namespace CMSCore\DataSection\Resolvers;

use CMSCore\DataSection\Contracts\DataTypeResolverInterface;
use CMSCore\DataSection\QueryContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Base resolver implementing the pinned + dynamic merge strategy.
 *
 * Subclasses provide type-specific behaviour by overriding:
 *   - modelClass()     — the Eloquent model to query
 *   - applySort()      — maps sort_by keys to query order-by clauses
 *   - project()        — projects a model to a lean array shape
 *   - previewColumns() — column definitions for the admin preview table
 *   - withRelations()  — (optional) eager-loads for both pinned and dynamic queries
 *   - constraints()    — (optional) column filters narrowing the candidate set
 *
 * Merge rules:
 *   1. Pinned records (selected_ids) are fetched by ID and restored to their
 *      user-defined order in PHP — database ordering is irrelevant here.
 *   2. Dynamic records fill the remaining capacity (limit - pinned count),
 *      ordered by applySort(), excluding already-pinned IDs.
 *   3. If no limit is set, dynamic records are unbounded (use with care).
 */
abstract class AbstractTypeResolver implements DataTypeResolverInterface
{
    /**
     * The fully-qualified Eloquent model class to query.
     */
    abstract protected function modelClass(): string;

    /**
     * Project an Eloquent model to a lean, API-safe array.
     *
     * @return array<string, mixed>
     */
    abstract protected function project(Model $record): array;

    /**
     * Apply a sort_by key to the query builder.
     * Return the query unchanged for unknown/unsupported keys.
     */
    abstract protected function applySort(Builder $query, string $sortBy): Builder;

    /**
     * Add eager-loads or query scopes needed by project().
     * Called for both the pinned fetch and the dynamic fetch.
     * Override to add with(), withCount(), etc.
     */
    protected function withRelations(Builder $query): Builder
    {
        return $query;
    }

    /**
     * Column constraints applied to every query for this type, pinned and
     * dynamic alike. Needed where one table backs more than one data type.
     *
     * @return array<string, mixed>
     */
    protected function constraints(): array
    {
        return [];
    }

    /** {@inheritDoc} */
    final public function resolve(QueryContext $context): Collection
    {
        $pinned  = $this->fetchPinned($context);
        $dynamic = $this->fetchDynamic($context, $pinned->pluck('id')->all());

        return $pinned->concat($dynamic);
    }

    /**
     * Fetch pinned records in the order defined by selected_ids.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function fetchPinned(QueryContext $context): Collection
    {
        if (! $context->hasPinnedIds()) {
            return collect();
        }

        $ids     = $context->selectedIds;
        $records = $this->withRelations($this->baseQuery())
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        // Restore user-defined pin order
        return collect($ids)
            ->map(fn (int $id) => $records->get($id))
            ->filter()
            ->map(fn (Model $r) => $this->project($r))
            ->values();
    }

    /**
     * Fetch dynamic records, honouring sort and remaining limit.
     *
     * @param  array<int, int> $excludeIds IDs already covered by the pinned fetch.
     * @return Collection<int, array<string, mixed>>
     */
    private function fetchDynamic(QueryContext $context, array $excludeIds): Collection
    {
        $dynamicLimit = $context->dynamicLimit();

        // Limit is fully satisfied by pinned records — skip the DB round-trip.
        if ($dynamicLimit !== null && $dynamicLimit <= 0) {
            return collect();
        }

        $query = $this->baseQuery();

        if (! empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }

        $query = $context->sortBy !== null
            ? $this->applySort($query, $context->sortBy)
            : $query->orderByDesc('created_at');

        if ($dynamicLimit !== null) {
            $query->limit($dynamicLimit);
        } elseif ($context->limit !== null) {
            // No pinned items but a limit was set — apply it directly.
            $query->limit($context->limit);
        }

        return $this->withRelations($query)
            ->get()
            ->map(fn (Model $r) => $this->project($r));
    }

    private function baseQuery(): Builder
    {
        $query = ($this->modelClass())::query();

        foreach ($this->constraints() as $column => $value) {
            $query->where($column, $value);
        }

        return $query;
    }
}

<?php

namespace CMSCore\DataSection\Contracts;

use CMSCore\DataSection\QueryContext;
use Illuminate\Support\Collection;

/**
 * Contract for per-type data resolvers.
 *
 * Implementors are responsible for:
 *   - Fetching pinned records (selected_ids) in their defined order
 *   - Fetching dynamic records using the requested sort strategy
 *   - Merging the two sets up to the configured limit
 *   - Projecting Eloquent models to a lean, API-safe array shape
 *
 * The base class AbstractTypeResolver provides the pinned+dynamic merge
 * strategy. Implementors typically only need to override modelClass(),
 * applySort(), project(), and previewColumns().
 */
interface DataTypeResolverInterface
{
    /**
     * Resolve records for the given query context.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function resolve(QueryContext $context): Collection;

    /**
     * Column definitions for the admin preview table.
     * Each entry must have 'key' (matches a key in the projected array)
     * and 'label' (display name).
     *
     * @return array<int, array{key: string, label: string}>
     */
    public function previewColumns(): array;
}

<?php

namespace CMSCore\DataSection;

use CMSCore\DataSection\Contracts\DataTypeResolverInterface;
use CMSCore\Enums\DataSectionType;
use CMSCore\Models\DataSection;
use CMSCore\Models\DataSectionItem;
use CMSCore\Models\Page;
use Illuminate\Support\Collection;

/**
 * Central entry point for resolving DataSection data.
 *
 * Registered as a singleton in CMSCoreServiceProvider so resolver
 * instances are reused across calls within the same request.
 *
 * Usage:
 *
 *   $resolver = app(DataSectionResolver::class);
 *
 *   // All visible sections for a page, keyed by identifier:
 *   $sections = $resolver->resolvePage($page);
 *
 *   // A single section:
 *   $result = $resolver->resolveSection($section);
 *
 *   // Low-level — resolve a raw QueryContext:
 *   $data = $resolver->resolveQuery($context);
 *
 * @see QueryContext
 * @see DataSectionResult
 */
class DataSectionResolver
{
    /** @var array<string, DataTypeResolverInterface> Per-request resolver cache. */
    private array $resolvers = [];

    /**
     * Resolve all sections for a page.
     *
     * @return array<string, DataSectionResult> Keyed by section identifier.
     */
    public function resolvePage(Page $page, bool $visibleOnly = true): array
    {
        $query = $page->dataSections()->with('items');

        if ($visibleOnly) {
            $query->where('is_visible', true);
        }

        return $query
            ->orderBy('sort_order')
            ->get()
            ->keyBy('identifier')
            ->map(fn (DataSection $section) => $this->resolveSection($section))
            ->all();
    }

    /**
     * Resolve a single DataSection.
     *
     * Resolution strategy:
     *   - If the section has DataSectionItems, each item is resolved as an
     *     independent QueryContext (sharing the section's data_type). Results
     *     are merged in sort_order and deduplicated by ID.
     *   - Otherwise the section's own selected_ids / sort_by / limit is used.
     */
    public function resolveSection(DataSection $section): DataSectionResult
    {
        $items = $section->relationLoaded('items')
            ? $section->items
            : $section->items()->get();
            ;
        $data = $items->isNotEmpty()
            ? $this->resolveFromItems($section, $items)
            : $this->resolveQuery(QueryContext::fromSection($section));
            
        $resolver = $this->getResolver($section->data_type);

        return new DataSectionResult(
            id: $section->id,
            identifier: $section->identifier,
            name: $section->name,
            dataType: $section->data_type->value,
            isVisible: $section->is_visible,
            data: $data,
            columns: $resolver->previewColumns(),
        );
    }

    /**
     * Resolve a raw QueryContext directly.
     * Public for admin preview endpoints and unit tests.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function resolveQuery(QueryContext $context): Collection
    {
        return $this->getResolver($context->type)->resolve($context);
    }

    /**
     * Column definitions for the given type's preview table.
     *
     * @return array<int, array{key: string, label: string, type: string}>
     */
    public function previewColumns(DataSectionType $type): array
    {
        return $this->getResolver($type)->previewColumns();
    }

    /**
     * Resolve each item independently and merge, deduplicating by record ID.
     *
     * @param Collection<int, DataSectionItem> $items
     * @return Collection<int, array<string, mixed>>
     */
    private function resolveFromItems(DataSection $section, Collection $items): Collection
    {
        $seen   = [];
        $merged = collect();

        foreach ($items as $item) {
            $records = $this->resolveQuery(QueryContext::fromItem($section, $item));

            foreach ($records as $record) {
                $id = $record['id'];
                if (! isset($seen[$id])) {
                    $seen[$id] = true;
                    $merged->push($record);
                }
            }
        }

        return $merged;
    }

    private function getResolver(DataSectionType $type): DataTypeResolverInterface
    {
        if (! isset($this->resolvers[$type->value])) {
            $class = $type->getResolverClass();
            $this->resolvers[$type->value] = new $class();
        }

        return $this->resolvers[$type->value];
    }
}

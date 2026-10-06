<?php

namespace CMSCore\DataSection;

use CMSCore\Enums\DataSectionType;
use CMSCore\Models\DataSection;
use CMSCore\Models\DataSectionItem;

/**
 * Immutable DTO describing a single query to be resolved.
 *
 * Built from a DataSection (section-level query) or a DataSectionItem
 * (sub-query within a section). Both carry the same shape so resolvers
 * do not need to know which source produced the context.
 */
readonly class QueryContext
{
    /**
     * @param array<int, int> $selectedIds Pinned record IDs in display order.
     */
    public function __construct(
        public DataSectionType $type,
        public array $selectedIds = [],
        public ?string $sortBy = null,
        public ?int $limit = null,
    ) {}

    public static function fromSection(DataSection $section): self
    {
        return new self(
            type: $section->data_type,
            selectedIds: $section->selected_ids ?? [],
            sortBy: $section->sort_by,
            limit: $section->limit,
        );
    }

    public static function fromItem(DataSection $section, DataSectionItem $item): self
    {
        return new self(
            type: $section->data_type,
            selectedIds: $item->selected_ids ?? [],
            sortBy: $item->sort_by,
            limit: $item->limit,
        );
    }

    public function hasPinnedIds(): bool
    {
        return count($this->selectedIds) > 0;
    }

    /**
     * How many additional dynamic records are needed to fill the limit
     * after accounting for the pinned set. Null means no limit applied.
     *
     * If IDs are pinned, selected_ids alone defines the result set — no
     * dynamic records are fetched regardless of the limit value.
     */
    public function dynamicLimit(): ?int
    {
        if ($this->hasPinnedIds()) {
            return 0;
        }

        return $this->limit;
    }
}

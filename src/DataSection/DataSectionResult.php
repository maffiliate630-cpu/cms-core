<?php

namespace CMSCore\DataSection;

use Illuminate\Support\Collection;

/**
 * Immutable DTO representing the fully resolved output of a DataSection.
 *
 * Returned by DataSectionResolver::resolveSection(). Suitable for both
 * the admin preview (via toArray()) and direct API responses.
 */
readonly class DataSectionResult
{
    /**
     * @param Collection<int, array<string, mixed>>        $data    Resolved records.
     * @param array<int, array{key: string, label: string}> $columns Preview column definitions.
     */
    public function __construct(
        public int $id,
        public string $identifier,
        public string $name,
        public string $dataType,
        public bool $isVisible,
        public Collection $data,
        public array $columns,
    ) {}

    /**
     * Full representation including admin-only column definitions.
     * Used by the cms-admin preview endpoint.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'identifier' => $this->identifier,
            'name'       => $this->name,
            'data_type'  => $this->dataType,
            'is_visible' => $this->isVisible,
            'columns'    => $this->columns,
            'data'       => $this->data->values()->all(),
            'count'      => $this->data->count(),
        ];
    }

    /**
     * Public API representation — excludes admin-only rendering metadata.
     * Used by cms-api responses.
     *
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        return [
            'id'         => $this->id,
            'identifier' => $this->identifier,
            'name'       => $this->name,
            'data_type'  => $this->dataType,
            'is_visible' => $this->isVisible,
            'count'      => $this->data->count(),
            'data'       => $this->data->values()->all(),
        ];
    }
}

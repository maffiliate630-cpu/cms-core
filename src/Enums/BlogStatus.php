<?php

namespace CMSCore\Enums;

enum BlogStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
    case SCHEDULED = 'scheduled';

    public static function options(): array
    {
        return array_map(static fn ($case) => [
            'value' => $case->value,
            'label' => ucfirst(strtolower($case->name)), // Draft, Published, etc.
        ], self::cases());
    }
}

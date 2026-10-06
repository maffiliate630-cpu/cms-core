<?php

namespace CMSCore\Queries\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class FilterMultipleFields implements Filter
{
    public function __construct(public $fields) {}

    public function __invoke(Builder $query, $value, string $property): Builder
    {
        $fields = $this->fields;

        $query->where(function ($query) use ($fields, $value) {
            foreach ($fields as $field) {
                $query->orWhere($field, 'ILIKE', '%'.$value.'%');
            }
        });

        return $query;
    }
}

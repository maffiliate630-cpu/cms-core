<?php

namespace CMSCore\Models;

use CMSCore\Models\Traits\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $data_section_id
 * @property array|null $selected_ids
 * @property string|null $sort_by
 * @property int|null $limit
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \CMSCore\Models\DataSection $dataSection
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DataSectionItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DataSectionItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DataSectionItem query()
 *
 * @mixin \Eloquent
 */
class DataSectionItem extends TenantModel
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'selected_ids' => 'array',
            'limit'        => 'integer',
            'sort_order'   => 'integer',
        ];
    }

    public function dataSection(): BelongsTo
    {
        return $this->belongsTo(DataSection::class);
    }
}

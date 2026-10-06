<?php

namespace CMSCore\Models;

use CMSCore\Enums\DataSectionType;
use CMSCore\Models\Traits\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $page_id
 * @property string $name
 * @property string $identifier
 * @property DataSectionType $data_type
 * @property bool $is_visible
 * @property array|null $selected_ids
 * @property string|null $sort_by
 * @property int|null $limit
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \CMSCore\Models\Page $page
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DataSection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DataSection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DataSection query()
 *
 * @mixin \Eloquent
 */
class DataSection extends TenantModel
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_visible'   => 'boolean',
            'data_type'    => DataSectionType::class,
            'selected_ids' => 'array',
            'limit'        => 'integer',
            'sort_order'   => 'integer',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DataSectionItem::class)->orderBy('sort_order');
    }
}

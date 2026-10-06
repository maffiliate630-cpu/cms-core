<?php

namespace CMSCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A currency products are priced in. Lives in the landlord schema and is
 * shared by every site; `products.currency_id` points at it without a foreign
 * key, since products are per-tenant.
 *
 * @property int $id
 * @property string $name
 * @property string $code ISO 4217 code, upper-case.
 * @property string $symbol
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @mixin \Eloquent
 */
class Currency extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class, 'currency_site')
            ->withTimestamps();
    }
}

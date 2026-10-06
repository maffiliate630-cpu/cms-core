<?php

namespace CMSCore\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Multitenancy\Models\Tenant as BaseTenant;

/**
 * @property int $id
 * @property string $name
 * @property string $domain
 * @property string|null $database
 * @property string|null $schema
 * @property string $identifier
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Spatie\Multitenancy\TenantCollection<int, static> all($columns = ['*'])
 * @method static \Spatie\Multitenancy\TenantCollection<int, static> get($columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereDatabase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereSchema($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Tenant extends BaseTenant implements AuthenticatableContract
{
    use Authenticatable, HasApiTokens;
    //    protected $casts = [
    //        'id' => 'string',
    //    ];
    protected $guarded = [
        'id',
    ];

    /**
     **************************************
     *  SCOPES
     **************************************
     */

    /**
     * Scope a query by the UUID identifier.
     */
    public function scopeWhereIdentifier(Builder $query, string $identifier): Builder
    {
        return $query->where('identifier', $identifier);
    }

    /**
     **************************************
     * RELATIONS
     **************************************
     */
    public function site(): HasOne
    {
        return $this->hasOne(Site::class, 'tenant_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\User')->withTimestamps();
    }

    /**
     **************************************
     * RELATIONS
     **************************************
     */
    public static function getBaseTenant(): static
    {
        return static::where('schema', 'base')->first();
    }
}

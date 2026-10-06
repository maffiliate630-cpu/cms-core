<?php

namespace CMSCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int|null $tenant_id
 * @property string $identifier
 * @property string $type
 * @property string $domain
 * @property string $title
 * @property string|null $tagline
 * @property string|null $settings
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \CMSCore\Models\SiteDetails|null $details
 * @property-read \CMSCore\Models\Tenant|null $tenant
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\Country> $countries
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Site newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Site newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Site query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Site whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Site whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Site whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Site whereIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Site whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Site whereSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Site whereTagline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Site whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Site whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Site whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Site whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Site extends Model
{
    protected $guarded = [
        'id',
        'tenant_id',
    ];

    protected static function booted(): void
    {
        static::created(static function (Site $site) {
            $site->details()->create();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'identifier';
    }

    /**
     **************************************************************************
     * RELATIONS
     **************************************************************************
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function details(): HasOne
    {
        return $this->hasOne(SiteDetails::class, 'site_id');
    }

    public function countries(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Country', 'site_country')
            ->withPivot('is_default')
            ->withTimestamps();
    }

    /**
     **************************************************************************
     * HELPERS
     **************************************************************************
     */
    public function attachTenant(Tenant $tenant): void
    {
        $this->tenant()->associate($tenant)->save();
    }

    public function detachTenant(): void
    {
        $this->tenant()->dissociate()->save();
    }



}

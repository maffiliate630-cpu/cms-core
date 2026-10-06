<?php

namespace CMSCore\Models\Traits;

use CMSCore\Models\Tenant;
use Illuminate\Support\Collection;

/**
 * @method tenants()
 */
trait HasTenants
{
    public function assignTenant(int|Tenant $tenant): static
    {
        $this->tenants()->syncWithoutDetaching([
            $tenant instanceof Tenant ? $tenant->id : $tenant,
        ]);

        return $this;
    }

    public function assignTenants(array|Collection $tenants): static
    {
        $ids = collect($tenants)->map(
            fn ($t) => $t instanceof Tenant ? $t->id : $t
        )->all();

        $this->tenants()->syncWithoutDetaching($ids);

        return $this;
    }

    public function syncTenants(array|Collection $tenants): static
    {
        $ids = collect($tenants)->map(
            fn ($t) => $t instanceof Tenant ? $t->id : $t
        )->all();

        $this->tenants()->sync($ids);

        return $this;
    }

    public function removeTenant(int|Tenant $tenant): static
    {
        $this->tenants()->detach(
            $tenant instanceof Tenant ? $tenant->id : $tenant
        );

        return $this;
    }

    public function hasTenant(int|Tenant $tenant): bool
    {
        $id = $tenant instanceof Tenant ? $tenant->id : $tenant;

        return $this->tenants->where('id', $id)->isNotEmpty();
    }

    public function getTenantIds(): array
    {
        return $this->tenants->pluck('id')->all();
    }
}

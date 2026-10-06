<?php

namespace CMSCore\Repositories;

use CMSCore\Models\Tenant;

class TenantRepository
{
    public function find(int $id): ?Tenant
    {
        return Tenant::find($id);
    }

    public function findBySchema(string $schema): ?Tenant
    {
        return Tenant::where('schema', $schema)->first();
    }

    public function findByIdentifier(string $identifier): ?Tenant
    {
        return Tenant::where('identifier', $identifier)->first();
    }

    /**
     * Check if a tenant exists by schema or name.
     */
    public function exists(?string $schema = null, ?string $name = null): bool
    {
        if (!$schema && !$name) return false;

        $query = Tenant::query();

        if ($schema !== null) {
            $query->where('schema', $schema);
        }

        if ($name !== null) {
            $query->where('name', 'ilike', $name);
        }

        return $query->exists();
    }

    /**
     * IMPORTANT: use TenantManagementService::create() to create a new tenant (it internally utilizes TenantRepository)
     */
    public function create(array $data): Tenant
    {
        // name, domain, schema
        return Tenant::create($data);
    }

    public function update(Tenant $tenant, array $data): Tenant
    {
        $tenant->update($data);

        return $tenant;
    }

    public function delete(Tenant $tenant): bool
    {
        return $tenant->delete();
    }
}

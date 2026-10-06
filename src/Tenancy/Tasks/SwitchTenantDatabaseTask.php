<?php

namespace CMSCore\Tenancy\Tasks;

use CMSCore\Models\Tenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

class SwitchTenantDatabaseTask implements SwitchTenantTask
{
    public function makeCurrent(Tenant|\Spatie\Multitenancy\Contracts\IsTenant $tenant): void
    {
        app('db')->connection('tenant')
            ->getPdo()
            ->exec("SET search_path TO {$tenant->schema}, public");
    }

    public function forgetCurrent(): void
    {
        // Reset back to public schema
        app('db')->connection('tenant')
            ->getPdo()
            ->exec('SET search_path TO public');
    }
}

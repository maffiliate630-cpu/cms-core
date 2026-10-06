<?php

use CMSCore\Models\Tenant;

function get_tenant_table(string $baseTable): string
{
    $tenant = Tenant::current();

    if (! $tenant) {
        throw new \RuntimeException('No current tenant set.');
    }

    return $tenant->schema.'.'.$baseTable;
}

<?php

namespace CMSCore\Tenancy\Finder;

use CMSCore\Models\Tenant;
use Illuminate\Http\Request;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

class SessionTenantFinder extends TenantFinder
{
    public function findForRequest(Request $request): ?Tenant
    {
        return Tenant::current();

        // $tenant = app(TenantContextService::class)->resolveTenantFromContext($request);
        //
        // if (! $tenant) {
        //     abort(401, 'Tenant not accessible');
        // }

        // return $tenant;
    }
}

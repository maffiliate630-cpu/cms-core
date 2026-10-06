<?php

namespace CMSCore\Services;

use CMSCore\Models\Tenant;
use CMSCore\Repositories\TenantRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Core tenant context service — handles tenant activation and resolution.
 *
 * This service is intentionally free of session, cookie, header, or APP_TYPE
 * concerns. App-specific layers (cms-admin, cms-api) extend this service and
 * override resolveTenant(), and ensureAuthorized() to add
 * their own resolution strategies and authorization logic.
 */
class TenantContextService
{
    public function __construct(
        protected TenantRepository $tenantRepository,
    ) {}

    /**
     * Full tenant initialization for a request.
     * Ensures authorization, resolves tenant, and activates it.
     *
     * @param Request $request
     */
    public function initialize(Request $request): void
    {
        $this->ensureAuthorized($request);

        $tenant = $this->resolveTenant($request);

        if (! $tenant) {
            abort(401, 'Invalid tenant');
        }

        $this->activateTenant($tenant);
    }

    /**
     * Activate a tenant as the current tenant context.
     * @param Tenant $tenant
     */
    public function activateTenant(Tenant $tenant): void
    {
        $current = Tenant::current();

        if ($current && $current->id === $tenant->id) {
            return;
        }

        $tenant->makeCurrent();

        Log::debug('TenantContextService: Activated tenant', [
            'tenant_id' => $tenant->id,
        ]);
    }

    /**
     * Resolve the tenant for the current request.
     *
     * @param Request $request
     * @return Tenant|null
     */
    public function resolveTenant(Request $request): ?Tenant
    {
        return $this->getBaseTenant();
    }

    /**
     * Resolve a Tenant by its ID, with an optional fallback.
     *
     * @param int|null $tenantId
     * @param Tenant|null $defaultTenant
     * @return Tenant|null
     */
    public function resolveTenantFromId(?int $tenantId, ?Tenant $defaultTenant = null): ?Tenant
    {
        if ($tenantId === null) {
            return $defaultTenant;
        }

        return $this->tenantRepository->find($tenantId) ?? $defaultTenant;
    }

    /**
     * Resolve a Tenant by its string identifier (UUID).
     *
     * @param string $identifier
     * @return Tenant|null
     */
    public function resolveTenantFromIdentifier(string $identifier): ?Tenant
    {
        return $this->tenantRepository->findByIdentifier($identifier);
    }

    /**
     *
     * @param Request $request
     */
    public function ensureAuthorized(Request $request): void
    {
        // Base implementation — no authorization required.
        // Override in subclasses to enforce auth (e.g., abort 401).
    }

    public function getBaseTenant(): Tenant
    {
        return Tenant::getBaseTenant();
    }
}

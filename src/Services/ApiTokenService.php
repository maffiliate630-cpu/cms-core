<?php

namespace CMSCore\Services;

use CMSCore\Models\Tenant;

/**
 * Manages permanent API keys (api_tokens table) for tenants.
 */
class ApiTokenService
{
    /**
     * Generate a new permanent API key for the given tenant.
     *
     * The plain-text token is only available at creation time — it is not
     * stored and cannot be recovered. Callers must surface it to the user.
     *
     * @return string Plain-text key in "{id}|{token}" Sanctum format.
     */
    public function generate(Tenant $tenant): string
    {
        $result = $tenant->createToken('api-key', ['exchange']);

        return $result->plainTextToken;
    }

    /**
     * Revoke all existing API keys for the tenant and issue a fresh one.
     *
     * @return string Plain-text key in "{id}|{token}" Sanctum format.
     */
    public function regenerate(Tenant $tenant): string
    {
        $tenant->tokens()->delete();

        return $this->generate($tenant);
    }
}

<?php

namespace CMSCore\Models;

use CMSCore\Models\Traits\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AffiliateLink extends TenantModel
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get the redirection (inbound) url for an affiliate
     *
     * @param  string|null  $domain  domain to build the URL for, if empty uses the current tenant's domain
     */
    public function getUrl(?string $domain = null): string
    {
        if ($domain === null) {
            $tenant = Tenant::current();
            $domain = str($tenant->site->domain)->rtrim('/');
        }

        $prefix = config('const.affiliate_url_prefix', '/r');

        return "https://{$domain}{$prefix}/{$this->slug}";
    }
}

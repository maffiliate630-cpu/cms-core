<?php

namespace CMSCore\Console\Commands;

use CMSCore\Models\Tenant;
use Illuminate\Console\Command;

class SwitchTenant extends Command
{
    /**
     * The command signature now defines two optional named options: --tenant_id and --schema.
     * We make them optional here and enforce the "at least one required" rule in the handle() method.
     *
     * @var string
     */
    protected $signature = 'tenant:switch
        {--tenant_id= : The ID of the tenant to switch to}
        {--schema= : The schema name of the tenant to switch to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Switch the current tenant context. Requires --tenant_id or --schema.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $tenantId = $this->option('tenant_id');
        $schema = $this->option('schema');
        $tenant = null;

        if (! $tenantId && ! $schema) {
            $this->error('❌ The --tenant_id or the --schema option must be provided.');

            return;
        }

        if ($tenantId) {
            $tenant = Tenant::find($tenantId);
        } elseif ($schema) {
            $tenant = Tenant::where('schema', $schema)->first();
        }

        if (! $tenant) {
            $this->error("Tenant not found with the following arguments: --id={$tenantId} --schema={$schema}");

            return;
        }

        $tenant->makeCurrent();

        $this->info("✅ Tenant successfully switched to: {$tenant->name} (Schema: {$tenant->schema}, ID: {$tenant->id})");
    }
}

<?php

namespace CMSCore\Tenancy\Tasks;

use CMSCore\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

class SwitchTenantDiskTask implements SwitchTenantTask
{
    /**
     * Builds a disk config scoped to a single tenant. Honours FILESYSTEM_DISK so
     * deployments can run tenant media off local disk or an S3 bucket without
     * code changes — only the underlying storage config differs.
     *
     * @return array<string, mixed>
     */
    private function getTenantDiskConfig(string $identifier): array
    {
        if (config('filesystems.default') === 's3') {
            return [
                'driver'                  => 's3',
                'key'                     => config('filesystems.disks.s3.key'),
                'secret'                  => config('filesystems.disks.s3.secret'),
                'region'                  => config('filesystems.disks.s3.region'),
                'bucket'                  => config('filesystems.disks.s3.bucket'),
                'endpoint'                => config('filesystems.disks.s3.endpoint'),
                'use_path_style_endpoint' => config('filesystems.disks.s3.use_path_style_endpoint'),
                // Scopes every object this disk instance touches under tenants/{identifier}/,
                // Flysystem's path prefixing applies it to reads, writes, and generated URLs alike.
                'root'                    => 'tenants/'.$identifier,
                // No 'visibility' here: setting it sends a public-read ACL on every
                // PutObject, which S3 rejects on buckets with ACLs disabled ("bucket
                // owner enforced" ownership). Public access must come from the bucket
                // policy instead, not per-object ACLs.
                'throw'                   => false,
                'report'                  => false,
            ];
        }

        $mediaBase = rtrim(env('MEDIA_URL', config('app.url')), '/');

        return [
            'driver'     => 'local',
            'root'       => storage_path('app/public/tenants/'.$identifier),
            'url'        => $mediaBase.'/storage/tenants/'.$identifier,
            'visibility' => 'public',
            'throw'      => false,
            'report'     => false,
        ];
    }

    public function makeCurrent(Tenant|IsTenant $tenant): void
    {
        $disk = config('media.tenant_disk');

        // Build a fresh Storage instance scoped to this tenant without mutating
        // shared config — config mutations persist across Octane workers/requests
        // and cause race conditions under concurrent load.
        $instance = Storage::build($this->getTenantDiskConfig($tenant->identifier));

        // Register under the canonical disk name so Storage::disk('tenant') resolves correctly.
        // Legacy: media records may have been stored with the identifier as the disk name.
        // Error is expected on those disks, to migrate to the new pattern.
        Storage::set($disk, $instance);

        Log::debug("switched tenant disk: {$tenant->identifier}");
    }

    public function forgetCurrent(): void
    {
        $disk = config('media.tenant_disk', 'tenant');
        $tenant = Tenant::current();

        Storage::forgetDisk($disk);

        if ($tenant) {
            Storage::forgetDisk($tenant->identifier);
        }

        Log::debug('switched to default public disk path');
    }
}

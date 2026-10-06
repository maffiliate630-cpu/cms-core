<?php

namespace CMSCore\Repositories;

use CMSCore\Models\Site;
use Illuminate\Support\Str;

class SiteRepository
{
    public function create(array $data, array $details): Site
    {
        $site = Site::create([
            'identifier' => Str::uuid(),
            ...$data,
        ]);

        $site->details()->updateOrCreate(
            ['site_id' => $site->id],
            $details,
        );

        return $site;
    }

    public function update(Site $site, array $data, array $details): Site
    {
        $site->update($data);

        $site->details()->update($details);

        return $site;
    }

    public function delete(Site $site): ?bool
    {
        return $site->delete();
    }
}

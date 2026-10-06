<?php

namespace CMSCore\Models;

use CMSCore\Models\Traits\TenantModel;

/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Datastore newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Datastore newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Datastore query()
 *
 * @mixin \Eloquent
 */
class Datastore extends TenantModel
{
    protected $table = 'datastore';

    protected $guarded = ['id'];

    protected $casts = [
        'payload' => 'array',
    ];
}

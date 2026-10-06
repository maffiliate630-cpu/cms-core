<?php

namespace CMSCore\Models\Traits;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantModel query()
 *
 * @mixin \Eloquent
 */
class TenantModel extends Model
{
    protected $connection = 'tenant';
}

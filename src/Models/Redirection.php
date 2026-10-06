<?php

namespace CMSCore\Models;

use CMSCore\Models\Traits\TenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @method static \Database\Factories\RedirectionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Redirection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Redirection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Redirection query()
 *
 * @mixin \Eloquent
 */
class Redirection extends TenantModel
{
    use HasFactory;

    protected $fillable = [
        'from_url',
        'to_url',
        'status_code',
        'is_active',
        'hits',
        'title',
    ];
}

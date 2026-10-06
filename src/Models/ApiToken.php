<?php

namespace CMSCore\Models;

use Laravel\Sanctum\PersonalAccessToken;

/**
 * Custom Sanctum token model using the `api_tokens` table.
 *
 * @property int $id
 * @property string $tokenable_type
 * @property int $tokenable_id
 * @property string $name
 * @property string $token
 * @property array<string>|null $abilities
 * @property string|null $created_ip
 * @property string|null $last_used_ip
 * @property \Illuminate\Support\Carbon|null $last_used_at
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class ApiToken extends PersonalAccessToken
{
    protected $guarded = ['id'];

    protected $table = 'api_tokens';
}

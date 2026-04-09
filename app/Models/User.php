<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password',
        'two_factor_secret', 'two_factor_enabled', 'two_factor_confirmed_at',
        'two_factor_recovery_codes',
        'two_factor_email_code', 'two_factor_email_code_expires_at',
    ];

    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'                 => 'datetime',
            'password'                          => 'hashed',
            'two_factor_enabled'                => 'boolean',
            'two_factor_confirmed_at'           => 'datetime',
            'two_factor_recovery_codes'         => 'array',
            'two_factor_email_code_expires_at'  => 'datetime',
        ];
    }
}

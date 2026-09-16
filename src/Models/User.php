<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Models;

use Alumkit\Alumkit\Traits\HasCareers;
use Alumkit\Alumkit\Traits\HasEducations;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property string $state
 *
 * @method Profile profile()
 */
class User extends Authenticatable implements MustVerifyEmailContract
{
    use HasCareers;
    use HasEducations;
    use HasRoles;
    use MustVerifyEmail;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'state',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}

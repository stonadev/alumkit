<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Actions\Fortify;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * @param  array<string, string>  $input
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
        ])->validate();

        if ($input['email'] !== $user->getAttribute('email')) {
            $user->forceFill([
                'email_verified_at' => null,
            ]);
        }

        $user->forceFill([
            'email' => $input['email'],
        ])->save();
    }
}

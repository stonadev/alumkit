<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Actions\Fortify;

use Alumkit\Alumkit\Enums\UserState;
use Alumkit\Alumkit\Http\Requests\RegisterUserRequest;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    /**
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): User
    {
        $request = new RegisterUserRequest;

        Validator::make($input, $request->rules())->validate();

        /** @var User */
        $user = config('alumkit.auth.user_model')::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'state' => config('alumkit.default_state', UserState::Pending)->value,
        ]);

        foreach ($input['educations'] as $education) {
            /** @phpstan-ignore method.notFound */
            $user->educations()->create($education);
        }

        return $user;
    }
}

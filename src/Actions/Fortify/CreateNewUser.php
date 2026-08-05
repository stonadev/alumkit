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

        $validated = Validator::make($input, $request->rules())->validate();

        /** @var User $user */
        $user = config('alumkit.auth.user_model')::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'state' => config('alumkit.default_state', UserState::Pending)->value,
        ]);

        return $user;
    }
}

<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Actions\Fortify;

use Alumkit\Alumkit\Enums\UserState;
use Alumkit\Alumkit\Http\Requests\RegisterUserRequest;
use Alumkit\Alumkit\Models\Profile;
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

        $validator = Validator::make($input, $request->rules());

        $validator->after(function (\Illuminate\Validation\Validator $validator) use ($input): void {
            foreach ($input['educations'] ?? [] as $i => $education) {
                if (empty($education['end_year']) && empty($education['is_current'])) {
                    $validator->errors()->add("educations.{$i}.end_year", __('validation.required', ['attribute' => 'end year']));
                }
            }
        });

        $validated = $validator->validate();

        /** @var User $user */
        $user = config('alumkit.auth.user_model')::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'state' => config('alumkit.default_state', UserState::Registered)->value,
        ]);

        /** @var Profile $profile */
        /** @phpstan-ignore method.notFound */
        $profile = $user->profile()->firstOrCreate();

        foreach ($validated['educations'] as $education) {
            /** @phpstan-ignore method.notFound */
            $profile->educations()->create($education);
        }

        return $user;
    }
}

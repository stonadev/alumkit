<?php

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Workbench\App\Models\User;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<User>
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->unique()->phoneNumber(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'state' => 'pending',
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the user has a pending profile awaiting review.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => 'pending',
        ]);
    }

    /**
     * Indicate that the user is approved (active state, email verified).
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => 'active',
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Attach a complete profile (gender + blood_group) to the user.
     */
    public function withProfile(array $attributes = []): static
    {
        return $this->afterCreating(function (User $user) use ($attributes): void {
            $user->profile()->create(array_merge([
                'gender' => 'male',
                'blood_group' => 'O+',
            ], $attributes));
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}

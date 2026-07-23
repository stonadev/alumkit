<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Actions\Fortify;

use Illuminate\Validation\Rules\Password;

trait PasswordValidationRules
{
    /**
     * @return array<int, Password|string>
     */
    protected function passwordRules(): array
    {
        return ['required', 'string', Password::defaults()];
    }
}

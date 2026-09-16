<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Traits;

use Alumkit\Alumkit\Models\Career;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasCareers
{
    /** @phpstan-ignore missingType.generics */
    public function careers(): HasMany
    {
        return $this->profile->careers();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function addCareer(array $attributes): Career
    {
        return $this->careers()->create($attributes); // @phpstan-ignore return.type
    }

    public function currentCareer(): ?Career
    {
        return $this->careers()->where('is_current', true)->first(); // @phpstan-ignore return.type
    }
}

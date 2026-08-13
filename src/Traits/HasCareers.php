<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Traits;

use Alumkit\Alumkit\Models\Career;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasCareers
{
    public function careers(): HasMany
    {
        return $this->profile->careers();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function addCareer(array $attributes): Career
    {
        return $this->careers()->create($attributes);
    }

    public function currentCareer(): ?Career
    {
        return $this->careers()->where('is_current', true)->first();
    }
}

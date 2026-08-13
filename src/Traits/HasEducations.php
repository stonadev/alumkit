<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Traits;

use Alumkit\Alumkit\Models\Education;
use Alumkit\Alumkit\Models\Profile;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait HasEducations
{
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function educations(): HasMany
    {
        return $this->profile->educations();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function addEducation(array $attributes): Education
    {
        return $this->educations()->create($attributes);
    }

    public function hasEducation(string $level): bool
    {
        return $this->educations()->where('level', $level)->exists();
    }
}

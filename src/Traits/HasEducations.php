<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Traits;

use Alumkit\Alumkit\Models\Education;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasEducations
{
    public function educations(): HasMany
    {
        return $this->hasMany(Education::class);
    }

    public function addEducation(array $attributes): Education
    {
        return $this->educations()->create($attributes);
    }

    public function hasEducation(string $level): bool
    {
        return $this->educations()->where('level', $level)->exists();
    }
}

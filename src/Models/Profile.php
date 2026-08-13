<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profile extends Model
{
    /** @var string */
    protected $table = 'profiles';

    /** @var list<string> */
    protected $fillable = [
        'user_id',
    ];

    /**
     * @phpstan-ignore missingType.generics
     */
    public function user(): BelongsTo
    {
        /** @phpstan-ignore argument.templateType */
        return $this->belongsTo(config('alumkit.auth.user_model'));
    }

    /**
     * @phpstan-ignore missingType.generics
     */
    public function educations(): HasMany
    {
        return $this->hasMany(Education::class);
    }

    /**
     * @phpstan-ignore missingType.generics
     */
    public function careers(): HasMany
    {
        return $this->hasMany(Career::class);
    }
}

<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    /** @var string */
    protected $table = 'positions';

    /** @var list<string> */
    protected $fillable = [
        'name',
    ];

    /**
     * @phpstan-ignore missingType.generics
     */
    public function committeeMembers(): HasMany
    {
        return $this->hasMany(CommitteeMember::class);
    }
}

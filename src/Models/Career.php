<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Models;

use Alumkit\Alumkit\Enums\EmploymentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property bool $is_current
 * @property int|null $end_year
 */
class Career extends Model
{
    /** @var string */
    protected $table = 'careers';

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'job_title',
        'company',
        'employment_type',
        'industry',
        'location',
        'start_year',
        'start_month',
        'is_current',
        'end_year',
        'end_month',
        'description',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'employment_type' => EmploymentType::class,
            'start_year' => 'integer',
            'start_month' => 'integer',
            'is_current' => 'boolean',
            'end_year' => 'integer',
            'end_month' => 'integer',
        ];
    }

    public function isCurrent(): bool
    {
        return $this->is_current;
    }

    /**
     * @phpstan-ignore missingType.generics
     */
    public function user(): BelongsTo
    {
        /** @phpstan-ignore argument.templateType */
        return $this->belongsTo(config('alumkit.auth.user_model'));
    }
}

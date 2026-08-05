<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Models;

use Alumkit\Alumkit\Enums\EducationLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Education extends Model
{
    /** @var string */
    protected $table = 'educations';

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'level',
        'institution',
        'subject',
        'start_year',
        'start_month',
        'end_year',
        'end_month',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'level' => EducationLevel::class,
            'start_year' => 'integer',
            'start_month' => 'integer',
            'end_year' => 'integer',
            'end_month' => 'integer',
        ];
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

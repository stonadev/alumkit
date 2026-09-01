<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Education extends Model
{
    use LogsActivity;

    /** @var string */
    protected $table = 'educations';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->logExcept(['created_at', 'updated_at']);
    }

    /** @var list<string> */
    protected $fillable = [
        'profile_id',
        'level',
        'institution',
        'student_id',
        'subject',
        'is_current',
        'start_year',
        'start_month',
        'end_year',
        'end_month',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_current' => 'boolean',
            'start_year' => 'integer',
            'start_month' => 'integer',
            'end_year' => 'integer',
            'end_month' => 'integer',
        ];
    }

    /**
     * @phpstan-ignore missingType.generics
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}

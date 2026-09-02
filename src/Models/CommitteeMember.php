<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int|null $position_id
 * @property int|null $user_id
 * @property string|null $name
 * @property string|null $photo_path
 * @property int $sort_order
 */
class CommitteeMember extends Model
{
    /** @var string */
    protected $table = 'committee_members';

    /** @var list<string> */
    protected $fillable = [
        'position_id',
        'user_id',
        'name',
        'photo_path',
        'sort_order',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    /**
     * @phpstan-ignore missingType.generics
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * @phpstan-ignore missingType.generics
     */
    public function user(): BelongsTo
    {
        /** @phpstan-ignore argument.templateType */
        return $this->belongsTo(config('alumkit.auth.user_model'));
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path
            ? route('alumkit.committee.photo', basename($this->photo_path))
            : null;
    }

    public function displayName(): string
    {
        /** @phpstan-ignore property.notFound */
        $userName = $this->user?->name;

        return $userName ?? $this->name ?? '—';
    }
}

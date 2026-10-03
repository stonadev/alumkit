<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Models;

use Alumkit\Alumkit\Enums\MembershipStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $membership_plan_id
 * @property string $status
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 * @property Carbon|null $cancelled_at
 * @property string|null $notes
 * @property int|null $created_by
 */
class Membership extends Model
{
    /** @var string */
    protected $table = 'memberships';

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'membership_plan_id',
        'status',
        'starts_at',
        'ends_at',
        'cancelled_at',
        'notes',
        'created_by',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /** @phpstan-ignore missingType.generics */
    public function user(): BelongsTo
    {
        /** @phpstan-ignore argument.templateType */
        return $this->belongsTo(config('alumkit.auth.user_model'));
    }

    /** @return BelongsTo<MembershipPlan, $this> */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class, 'membership_plan_id');
    }

    /** @return HasMany<MembershipPayment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(MembershipPayment::class);
    }

    public function isLifetime(): bool
    {
        return $this->plan?->isLifetime() ?? false;
    }

    public function statusEnum(): MembershipStatus
    {
        return MembershipStatus::from($this->status);
    }

    /**
     * A membership is active while its status is active and either it is
     * lifetime or its end date is still in the future.
     */
    public function isActive(): bool
    {
        if ($this->status !== MembershipStatus::Active->value) {
            return false;
        }

        if ($this->isLifetime()) {
            return true;
        }

        return $this->ends_at !== null && $this->ends_at->isFuture();
    }

    /**
     * Derive the status on read so a lapsed row never reads as active when a
     * scheduled sweep has not run yet.
     */
    public function effectiveStatus(): MembershipStatus
    {
        $status = $this->statusEnum();

        if ($status === MembershipStatus::Active && ! $this->isLifetime()
            && $this->ends_at !== null && ! $this->ends_at->isFuture()) {
            return MembershipStatus::Expired;
        }

        return $status;
    }
}

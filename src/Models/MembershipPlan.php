<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string $price
 * @property int|null $duration_days
 * @property bool $is_lifetime
 * @property array<string, string>|null $features
 * @property int $sort_order
 * @property bool $is_active
 */
class MembershipPlan extends Model
{
    /** @var string */
    protected $table = 'membership_plans';

    /** @var list<string> */
    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_days',
        'is_lifetime',
        'features',
        'sort_order',
        'is_active',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'duration_days' => 'integer',
            'is_lifetime' => 'boolean',
            'features' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Enforce the plan invariants at the model layer, mirroring the form
     * requests. A plan holds exactly one term state: a base duration in days,
     * or lifetime (no end).
     */
    protected static function booted(): void
    {
        static::saving(function (self $plan): void {
            $lifetime = (bool) $plan->is_lifetime;

            if ($lifetime !== ($plan->duration_days === null)) {
                throw new InvalidArgumentException(
                    'A membership plan must set exactly one term: a base duration in days, or lifetime.',
                );
            }
        });
    }

    /**
     * @param  Builder<MembershipPlan>  $query
     * @return Builder<MembershipPlan>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /** @return HasMany<Membership, $this> */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    /** @return HasMany<MembershipPayment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(MembershipPayment::class);
    }

    public function isLifetime(): bool
    {
        return (bool) $this->is_lifetime;
    }

    /**
     * Human-readable term: "Lifetime", "7 days", or "12 months". A stored day
     * count that is a whole number of months (>= 2 months of 30 days) renders
     * as months; everything else renders as days.
     */
    public function termLabel(): string
    {
        if ($this->isLifetime()) {
            return __('alumkit::membership.term_lifetime');
        }

        $days = (int) $this->duration_days;

        if ($days % 30 === 0 && $days >= 60) {
            $months = intdiv($days, 30);

            return trans_choice('alumkit::membership.term_months', $months, ['count' => $months]);
        }

        return trans_choice('alumkit::membership.term_days', $days, ['count' => $days]);
    }

    public function hasFeature(string $key): bool
    {
        $value = $this->features[$key] ?? null;

        if ($value === null) {
            return false;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * The single term calculator. Returns the term end date for a membership
     * starting at $start; a lifetime term returns null (no end).
     */
    public function termEndFrom(Carbon $start): ?Carbon
    {
        if ($this->isLifetime()) {
            return null;
        }

        return $start->copy()->addDays((int) $this->duration_days);
    }
}

<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Traits;

use Alumkit\Alumkit\Enums\MembershipStatus;
use Alumkit\Alumkit\Models\Membership;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait HasMemberships
{
    /** @return HasMany<Membership, $this> */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    /** @return HasOne<Membership, $this> */
    public function activeMembership(): HasOne
    {
        return $this->hasOne(Membership::class)
            ->where('status', MembershipStatus::Active->value)
            ->where(function ($query): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>', now());
            });
    }

    /** @return HasOne<Membership, $this> */
    public function latestMembership(): HasOne
    {
        return $this->hasOne(Membership::class)->latestOfMany();
    }

    public function hasActiveMembership(): bool
    {
        return $this->memberships()
            ->where('status', MembershipStatus::Active->value)
            ->where(function ($query): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->exists();
    }

    /**
     * Read a feature value from the active membership's plan, or null when the
     * member has no active membership or the feature is not set.
     */
    public function membershipFeature(string $key): ?string
    {
        $membership = $this->activeMembership()->with('plan')->first();

        $value = $membership?->plan?->features[$key] ?? null;

        return $value === null ? null : (string) $value;
    }

    /**
     * Truthy feature gate: "1", "true", "yes", or "on" all count as enabled.
     */
    public function hasMembershipFeature(string $key): bool
    {
        $value = $this->membershipFeature($key);

        if ($value === null) {
            return false;
        }

        return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * Whether the user may reach a membership-gated dashboard feature.
     *
     * Gating is only active while the memberships feature is enabled. Staff who
     * administer memberships always bypass the gate; everyone else needs an
     * active membership whose plan grants the feature.
     */
    public function canAccessMembershipFeature(string $feature): bool
    {
        if (! config('alumkit.features.memberships')) {
            return true;
        }

        if ($this->isMembershipAdmin()) {
            return true;
        }

        return $this->hasActiveMembership() && $this->hasMembershipFeature($feature);
    }

    /** Staff administering memberships bypass membership feature gates. */
    protected function isMembershipAdmin(): bool
    {
        return $this->hasAnyPermission(['manage members', 'manage memberships', 'manage membership plans']);
    }
}

<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Actions;

use Alumkit\Alumkit\Models\Membership;
use Alumkit\Alumkit\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class UpdateMembership
{
    /**
     * Staff adjustment of a membership's end date and notes only.
     *
     * @param  array{ends_at?: Carbon|string|null, notes?: string|null}  $data
     */
    public function handle(Membership $membership, array $data, ?User $actor = null): Membership
    {
        $endsAt = array_key_exists('ends_at', $data) ? $data['ends_at'] : $membership->ends_at;
        $notes = array_key_exists('notes', $data) ? $data['notes'] : $membership->notes;

        if ($endsAt !== null && $membership->starts_at !== null) {
            $end = Carbon::parse($endsAt);

            if (! $end->greaterThan($membership->starts_at)) {
                throw ValidationException::withMessages([
                    'ends_at' => __('alumkit::membership.ends_after_start'),
                ]);
            }
        }

        $membership->update([
            'ends_at' => $endsAt,
            'notes' => $notes,
        ]);

        activity('memberships')
            ->performedOn($membership)
            ->event('membership_updated')
            ->withProperties([
                'ends_at' => $membership->ends_at?->toIso8601String(),
                'updated_by' => $actor?->getKey(),
            ])
            ->log('membership updated');

        return $membership;
    }
}

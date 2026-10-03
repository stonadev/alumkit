<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Console\Commands;

use Alumkit\Alumkit\Enums\MembershipStatus;
use Alumkit\Alumkit\Events\MembershipExpired;
use Alumkit\Alumkit\Models\Membership;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ExpireMembershipsCommand extends Command
{
    protected $signature = 'alumkit:memberships:expire';

    protected $description = 'Flip active, non-lifetime memberships whose end date has passed to expired.';

    public function handle(): int
    {
        $count = 0;

        Membership::query()
            ->where('status', MembershipStatus::Active->value)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now())
            ->orderBy('id')
            ->chunkById(100, /** @param Collection<int, Membership> $memberships */ function (Collection $memberships) use (&$count): void {
                foreach ($memberships as $membership) {
                    if ($membership->isLifetime()) {
                        continue;
                    }

                    $membership->update(['status' => MembershipStatus::Expired->value]);

                    MembershipExpired::dispatch($membership);

                    $count++;
                }
            });

        $this->info("Expired {$count} membership(s).");

        return self::SUCCESS;
    }
}

<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Events;

use Alumkit\Alumkit\Models\Membership;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MembershipActivated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Membership $membership,
    ) {}
}

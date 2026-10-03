<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Events;

use Alumkit\Alumkit\Models\Membership;
use Alumkit\Alumkit\Models\MembershipPayment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentApproved
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public MembershipPayment $payment,
        public Membership $membership,
    ) {}
}

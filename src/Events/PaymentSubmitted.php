<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Events;

use Alumkit\Alumkit\Models\MembershipPayment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentSubmitted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public MembershipPayment $payment,
    ) {}
}

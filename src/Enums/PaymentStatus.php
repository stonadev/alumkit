<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}

<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Enums;

enum MembershipStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Cancelled = 'cancelled';

    /**
     * @return array<self>
     */
    public function transitions(): array
    {
        return match ($this) {
            self::Active => [self::Expired, self::Cancelled],
            self::Expired => [],
            self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->transitions(), true);
    }
}

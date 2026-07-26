<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Enums;

enum UserState: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Suspended = 'suspended';
    case Rejected = 'rejected';

    /**
     * @return array<self>
     */
    public function transitions(): array
    {
        return match ($this) {
            self::Pending => [self::Active, self::Rejected],
            self::Active => [self::Suspended],
            self::Suspended => [self::Active],
            default => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->transitions(), true);
    }
}

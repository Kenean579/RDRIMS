<?php

namespace App\Enums;

enum ProjectStatusEnum: int
{
    case ACTIVE = 1;
    case COMPLETED = 2;
    case SUSPENDED = 3;

    public function name(): string
    {
        return match($this) {
            self::ACTIVE => 'active',
            self::COMPLETED => 'completed',
            self::SUSPENDED => 'suspended',
        };
    }
}

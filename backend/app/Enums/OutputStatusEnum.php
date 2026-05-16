<?php

namespace App\Enums;

enum OutputStatusEnum: int
{
    case DRAFT = 1;
    case SUBMITTED = 2;
    case APPROVED_BY_SUPERVISOR = 3;
    case APPROVED = 4;
    case REJECTED = 5;

    public function name(): string
    {
        return match($this) {
            self::DRAFT => 'draft',
            self::SUBMITTED => 'submitted',
            self::APPROVED_BY_SUPERVISOR => 'approved_by_supervisor',
            self::APPROVED => 'approved',
            self::REJECTED => 'rejected',
        };
    }
}

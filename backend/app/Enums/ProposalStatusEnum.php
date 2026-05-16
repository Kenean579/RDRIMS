<?php

namespace App\Enums;

enum ProposalStatusEnum: int
{
    case DRAFT = 1;
    case SUBMITTED = 2;
    case UNDER_REVIEW = 3;
    case FINANCE_CHECK = 4;
    case APPROVED = 5;
    case REJECTED = 6;

    public function name(): string
    {
        return match($this) {
            self::DRAFT => 'draft',
            self::SUBMITTED => 'submitted',
            self::UNDER_REVIEW => 'under_review',
            self::FINANCE_CHECK => 'finance_check',
            self::APPROVED => 'approved',
            self::REJECTED => 'rejected',
        };
    }
}

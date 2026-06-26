<?php

namespace App\Enums;

use App\Models\ProposalStatus;

/**
 * Maps proposal status names to their database IDs.
 * Uses a backed enum (int) matching the auto-increment IDs
 * created by the ProposalStatusSeeder in order:
 *   1 = draft, 2 = submitted, 3 = checking,
 *   4 = under_review, 5 = finance_check, 6 = approved, 7 = rejected
 */
enum ProposalStatusEnum: int
{
    case DRAFT         = 1;
    case SUBMITTED     = 2;
    case CHECKING      = 3;
    case UNDER_REVIEW  = 4;
    case FINANCE_CHECK = 5;
    case APPROVED      = 6;
    case REJECTED      = 7;

    /**
     * Resolve the ID at runtime from the database.
     * Useful when seed order may differ from the hard-coded IDs above.
     */
    public static function idFor(string $name): int
    {
        return ProposalStatus::where('name', $name)->value('id');
    }

    /**
     * Get the human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT         => 'Draft',
            self::SUBMITTED     => 'Submitted',
            self::CHECKING      => 'Checking',
            self::UNDER_REVIEW  => 'Under Review',
            self::FINANCE_CHECK => 'Finance Check',
            self::APPROVED      => 'Approved',
            self::REJECTED      => 'Rejected',
        };
    }
}

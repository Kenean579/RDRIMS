<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case RESEARCH_ADMIN = 'research_admin';
    case DIRECTOR = 'director';
    case DEPARTMENT_HEAD = 'department_head';
    case RESEARCHER = 'researcher';
    case REVIEWER = 'reviewer';
    case FINANCE_OFFICER = 'finance_officer';
    case ETHICS_OFFICER = 'ethics_officer';
    case STUDENT = 'student';
    case GUEST = 'guest';

    public function label(): string
    {
        return match($this) {
            self::SUPER_ADMIN => 'Super Administrator',
            self::RESEARCH_ADMIN => 'Research Administrator',
            self::DIRECTOR => 'Director',
            self::DEPARTMENT_HEAD => 'Department Head',
            self::RESEARCHER => 'Researcher',
            self::REVIEWER => 'Reviewer',
            self::FINANCE_OFFICER => 'Finance Officer',
            self::ETHICS_OFFICER => 'Ethics Officer',
            self::STUDENT => 'Student',
            self::GUEST => 'Guest',
        };
    }
}

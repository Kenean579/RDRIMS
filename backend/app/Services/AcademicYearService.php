<?php

namespace App\Services;

use App\Models\AcademicYear;

class AcademicYearService
{
    public function getCurrent(): ?AcademicYear
    {
        return AcademicYear::where('is_current', true)->first();
    }

    public function setCurrent(AcademicYear $academicYear): void
    {
        AcademicYear::where('is_current', true)->update(['is_current' => false]);
        $academicYear->update(['is_current' => true]);
    }

    public function openCalls(AcademicYear $academicYear): void
    {
        $academicYear->update(['status' => 'calls_open']);
    }

    public function close(AcademicYear $academicYear): void
    {
        $academicYear->update(['status' => 'closed', 'is_current' => false]);
    }
}

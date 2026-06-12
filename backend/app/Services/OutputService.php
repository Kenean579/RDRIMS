<?php

namespace App\Services;

use App\Models\Output;
use App\Models\OutputStatus;
use App\Models\OutputCategory;
use App\Models\StudentLevel;
use App\Models\ParticipantType;
use App\Models\OutputSubtype;
use Illuminate\Support\Facades\DB;

class OutputService
{
    public function changeStatus(Output $output, $newStatusIdOrName): Output
    {
        $currentStatus = $output->status->name;

        if (is_numeric($newStatusIdOrName)) {
            $newStatus = OutputStatus::findOrFail($newStatusIdOrName);
            $newStatusName = $newStatus->name;
        } else {
            $newStatusName = $newStatusIdOrName;
            $newStatus = OutputStatus::where('name', $newStatusName)->firstOrFail();
        }

        $allowedTransitions = [
            'draft'                   => ['submitted'],
            'submitted'               => ['approved_by_supervisor', 'rejected'],
            'approved_by_supervisor'  => ['approved', 'rejected'],
        ];

        if (!isset($allowedTransitions[$currentStatus])) {
            // Allow admin to bypass? No, let's stick to logic but maybe allow same-status updates
            if ($currentStatus === $newStatusName) return $output;
            abort(422, "Status '{$currentStatus}' cannot be changed.");
        }

        if (!in_array($newStatusName, $allowedTransitions[$currentStatus])) {
             if ($currentStatus === $newStatusName) return $output;
            abort(422, "Cannot change status from '{$currentStatus}' to '{$newStatusName}'.");
        }

        $output->update(['status_id' => $newStatus->id]);

        return $output->refresh();
    }

    /**
     * Get allowed subtypes based on student level
     */
    public function getSubtypesByLevel($studentLevelId = null): array
    {
        if (!$studentLevelId) {
            return OutputSubtype::all()->toArray();
        }

        $level = StudentLevel::find($studentLevelId);
        if (!$level) {
            return OutputSubtype::all()->toArray();
        }

        $levelName = $level->name;

        // Define subtype mappings based on level
        $levelSubtypes = [
            'undergraduate' => ['internship', 'final_year_project', 'semester_project'],
            'graduate' => ['thesis', 'research_paper', 'dataset', 'report'],
            'phd' => ['thesis', 'research_paper', 'dataset', 'report'],
        ];

        $allowedSubtypeNames = $levelSubtypes[$levelName] ?? [];

        if (empty($allowedSubtypeNames)) {
            return OutputSubtype::all()->toArray();
        }

        return OutputSubtype::whereIn('name', $allowedSubtypeNames)->get()->toArray();
    }

    /**
     * Automatically add student as participant for student outputs
     */
    public function addStudentParticipant(Output $output, $userId): void
    {
        if ($output->category->name !== 'student') {
            return;
        }

        $studentType = ParticipantType::where('name', 'student')->first();
        if (!$studentType) {
            return;
        }

        // Check if student is already added
        $existing = $output->participants()
            ->where('user_id', $userId)
            ->where('participant_type_id', $studentType->id)
            ->first();

        if (!$existing) {
            $output->participants()->attach($userId, ['participant_type_id' => $studentType->id]);
        }
    }

    /**
     * Validate that supervisor is assigned for student outputs
     */
    public function validateStudentOutputParticipants(Output $output): bool
    {
        if ($output->category->name !== 'student') {
            return true; // Not a student output, no validation needed
        }

        $supervisorType = ParticipantType::where('name', 'supervisor')->first();
        if (!$supervisorType) {
            return false;
        }

        $hasSupervisor = $output->participants()
            ->wherePivot('participant_type_id', $supervisorType->id)
            ->exists();

        return $hasSupervisor;
    }
}
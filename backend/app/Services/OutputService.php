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
    /**
     * Create a new output with proper initialization
     */
    public function create(array $data, int $userId): Output
    {
        return DB::transaction(function () use ($data, $userId) {
            $data['created_by'] = $userId;
            $data['updated_by'] = $userId;
            
            // Get draft status
            $draftStatus = OutputStatus::where('name', 'draft')->first();
            if (!$draftStatus) {
                throw new \Exception('Draft status not found. Please seed output statuses.');
            }
            
            // Unguard to set protected fields
            Output::unguard();
            $data['status_id'] = $draftStatus->id;
            $output = Output::create($data);
            Output::reguard();
            
            return $output->fresh();
        });
    }

    /**
     * Update an output with audit logging
     */
    public function update(Output $output, array $data, int $userId): Output
    {
        return DB::transaction(function () use ($output, $data, $userId) {
            $data['updated_by'] = $userId;
            
            // Cannot update verified/published outputs
            if ($output->isVerified() || $output->isPublished()) {
                throw new \InvalidArgumentException('Cannot update verified or published outputs.');
            }
            
            $output->update($data);
            return $output->fresh();
        });
    }

    /**
     * Submit output for verification
     */
    public function submit(Output $output, int $userId): Output
    {
        return DB::transaction(function () use ($output, $userId) {
            // Validation: must be in draft status
            if (!$output->isDraft()) {
                throw new \InvalidArgumentException('Only draft outputs can be submitted');
            }
            
            // Validation: must have at least one participant
            if ($output->participants()->count() === 0) {
                throw new \InvalidArgumentException('Output must have at least one participant');
            }
            
            $submittedStatus = OutputStatus::where('name', 'submitted')->first();
            
            Output::unguard();
            $output->update([
                'status_id' => $submittedStatus->id,
                'updated_by' => $userId,
            ]);
            Output::reguard();
            
            return $output->fresh();
        });
    }

    /**
     * Verify output (admin only)
     */
    public function verify(Output $output, int $userId): Output
    {
        return DB::transaction(function () use ($output, $userId) {
            Output::unguard();
            $output->update([
                'verified_by' => $userId,
                'verified_at' => now(),
                'updated_by' => $userId,
            ]);
            Output::reguard();
            
            return $output->fresh();
        });
    }

    /**
     * Approve output (change status to approved)
     */
    public function approve(Output $output, int $userId): Output
    {
        return DB::transaction(function () use ($output, $userId) {
            $approvedStatus = OutputStatus::where('name', 'approved')->first();
            
            Output::unguard();
            $output->update([
                'status_id' => $approvedStatus->id,
                'updated_by' => $userId,
            ]);
            Output::reguard();
            
            return $output->fresh();
        });
    }

    /**
     * Reject output
     */
    public function reject(Output $output, int $userId, string $reason = ''): Output
    {
        return DB::transaction(function () use ($output, $userId, $reason) {
            $rejectedStatus = OutputStatus::where('name', 'rejected')->first();
            
            Output::unguard();
            $output->update([
                'status_id' => $rejectedStatus->id,
                'feedback' => ($output->feedback ?? '') . "\n[REJECTED] " . $reason,
                'updated_by' => $userId,
            ]);
            Output::reguard();
            
            return $output->fresh();
        });
    }

    /**
     * Publish output
     */
    public function publish(Output $output, int $userId): Output
    {
        return DB::transaction(function () use ($output, $userId) {
            if (!$output->canPublish()) {
                throw new \InvalidArgumentException('Output must be verified and approved before publishing');
            }
            
            $publishedStatus = OutputStatus::where('name', 'published')->first();
            
            Output::unguard();
            $output->update([
                'status_id' => $publishedStatus->id,
                'updated_by' => $userId,
            ]);
            Output::reguard();
            
            return $output->fresh();
        });
    }

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
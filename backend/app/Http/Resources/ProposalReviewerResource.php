<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProposalReviewerResource extends JsonResource
{
    /**
     * Determine if sensitive data should be exposed.
     * Only expose full details to admins and the reviewer themselves.
     */
    private function shouldExposeFullDetails(): bool
    {
        $user = request()->user();
        
        if (!$user) {
            return false;
        }

        // Super admins see everything
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Reviewers see only their own data
        if ($this->reviewer_id === $user->id) {
            return true;
        }

        // Admins see reviewers in their institution
        if ($user->isAdmin()) {
            $proposal = $this->relationLoaded('proposal')
                ? $this->getRelation('proposal')
                : $this->proposal;

            if ($proposal && $proposal->relationLoaded('submittedBy')) {
                $submittedBy = $proposal->getRelation('submittedBy');
            } else {
                $submittedBy = $proposal?->submittedBy;
            }

            if ($submittedBy instanceof \App\Models\User) {
                return $user->sharesInstitutionWith($submittedBy);
            }
        }

        return false;
    }

    public function toArray(Request $request): array
    {
        $exposeFullDetails = $this->shouldExposeFullDetails();

        $data = [
            'id' => $this->id,
            'assigned_at' => $this->assigned_at?->toISOString(),
            'submitted_at' => $this->submitted_at?->toISOString(),
        ];

        // Only show reviewer details to authorized users
        if ($exposeFullDetails) {
            $data['reviewer_id'] = $this->reviewer_id;
            $data['reviewer'] = [
                'id' => $this->reviewer->id ?? $this->reviewer_id,
                'name' => $this->reviewer->name ?? null,
                'email' => $this->reviewer->email ?? null,
            ];

            $data['assigned_by'] = $this->assigned_by;
            $data['overall_score'] = $this->overall_score;
            $data['overall_comments'] = $this->overall_comments;
            
            // Only show decision to authorized users
            if ($this->decision) {
                $data['decision'] = [
                    'id' => $this->decision->id,
                    'name' => $this->decision->name,
                ];
            }

            // Scores visible only to full details access
            if ($this->relationLoaded('scores')) {
                $data['scores'] = $this->scores->map(fn($score) => [
                    'criterion_id' => $score->criterion_id,
                    'criterion_name' => $score->criterion?->name,
                    'score' => $score->score,
                    'comments' => $score->comments,
                ]);
            }
        } else {
            // For other users, only indicate submission status
            $data['is_submitted'] = $this->submitted_at !== null;
            $data['reviewer_id'] = $this->reviewer_id;
        }

        return $data;
    }
}

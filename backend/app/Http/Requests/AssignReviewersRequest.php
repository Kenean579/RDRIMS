<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignReviewersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('proposal'));
    }

    public function rules(): array
    {
        $proposal = $this->route('proposal');
        $actor = $this->user();
        $investigatorIds = $proposal?->investigators()->whereNotNull('user_id')->pluck('user_id')->all() ?? [];

        return [
            'reviewer_ids' => 'required|array',
            'reviewer_ids.*' => [
                'integer',
                Rule::exists('users', 'id'),
                function (string $attribute, mixed $value, \Closure $fail) use ($actor, $proposal, $investigatorIds) {
                    if (! $actor || ! $proposal) {
                        $fail('Invalid reviewer assignment context.');
                        return;
                    }

                    // SECURITY: Explicit tenant scope verification
                    $submittedBy = $proposal->relationLoaded('submittedBy')
                        ? $proposal->getRelation('submittedBy')
                        : $proposal->submittedBy;

                    if (!$submittedBy) {
                        $fail('Proposal has no submitter information.');
                        return;
                    }

                    // Verify reviewer is in same institution as proposal submitter
                    $reviewer = \App\Models\User::find($value);
                    if (!$reviewer || !$actor->sharesInstitutionWith($submittedBy) || !$reviewer->sharesInstitutionWith($submittedBy)) {
                        $fail('Reviewer must be from the same institution as the proposal submitter.');
                        return;
                    }

                    $isEligible = User::query()
                        ->whereKey($value)
                        ->whereHas('roles', fn ($q) => $q->where('name', 'reviewer'))
                        ->hierarchical($actor, 'id')
                        ->where('id', '!=', $proposal->submitted_by)
                        ->whereNotIn('id', $investigatorIds)
                        ->exists();

                    if (! $isEligible) {
                        $fail('One or more reviewers are outside your institution scope or ineligible.');
                    }
                },
            ],
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Models\ReviewCriterion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SubmitReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        // SECURITY: Only assigned reviewers can submit reviews
        $proposal = $this->route('proposal');
        $user = $this->user();

        if (!$proposal || !$user) {
            return false;
        }

        // Verify user is actually assigned as a reviewer for this proposal
        return \App\Models\ProposalReviewer::where('proposal_id', $proposal->id)
            ->where('reviewer_id', $user->id)
            ->exists();
    }

    public function rules(): array
    {
        return [
            'scores' => 'required|array|min:1',
            'scores.*.criterion_id' => 'required|exists:review_criteria,id',
            'scores.*.score' => 'required|integer|min:0',
            'scores.*.comments' => 'nullable|string',
            'overall_score' => 'required|numeric|min:0|max:5',
            'overall_comments' => 'nullable|string',
            'decision_id' => 'required|exists:review_decisions,id',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $criteria = ReviewCriterion::whereIn(
                'id',
                collect($this->input('scores', []))->pluck('criterion_id')
            )->get()->keyBy('id');

            foreach ($this->input('scores', []) as $index => $scoreData) {
                $criterion = $criteria->get($scoreData['criterion_id'] ?? null);

                if (!$criterion) {
                    continue;
                }

                $score = $scoreData['score'] ?? null;

                if (!is_numeric($score) || $score > $criterion->max_score) {
                    $validator->errors()->add(
                        "scores.{$index}.score",
                        "Score for \"{$criterion->name}\" must not exceed {$criterion->max_score}."
                    );
                }
            }
        });
    }
}

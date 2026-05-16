<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'scores' => 'required|array',
            'scores.*.criterion_id' => 'required|exists:review_criteria,id',
            'scores.*.score' => 'required|integer|min:0',
            'scores.*.comments' => 'nullable|string',
            'overall_score' => 'required|numeric|min:0|max:100',
            'overall_comments' => 'nullable|string',
            'decision_id' => 'required|exists:review_decisions,id',
        ];
    }
}

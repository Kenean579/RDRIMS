<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
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
            'scores.*.score' => 'required|numeric|min:0',
            'general_comment' => 'nullable|string',
            'recommendation' => 'required|in:approve,reject,major_revision,minor_revision',
        ];
    }
}

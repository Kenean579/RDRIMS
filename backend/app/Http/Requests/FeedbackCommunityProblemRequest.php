<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FeedbackCommunityProblemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $problem = $this->route('problem');
        return $this->user()->can('addFeedback', $problem);
    }

    public function rules(): array
    {
        return [
            'feedback' => 'required|string',
            'rating'   => 'required|integer|min:1|max:5',
        ];
    }
}
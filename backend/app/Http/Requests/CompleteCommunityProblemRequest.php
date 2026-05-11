<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompleteCommunityProblemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $problem = $this->route('problem');
        return $this->user()->can('complete', $problem);
    }

    public function rules(): array
    {
        return [
            'linked_project_id' => 'nullable|exists:projects,id',
        ];
    }
}

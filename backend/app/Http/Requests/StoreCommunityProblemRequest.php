<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommunityProblemRequest extends FormRequest
{
    use \App\Traits\CastBooleanFields;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->castBooleans(['is_anonymous']);
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'contact_info' => 'nullable|string|max:255',
            'status_id' => 'nullable|exists:community_problem_statuses,id',
            'is_anonymous' => 'sometimes|boolean',
            'linked_project_id' => 'nullable|exists:projects,id',
            'research_center_id' => 'required|exists:research_centers,id',
        ];
    }
}
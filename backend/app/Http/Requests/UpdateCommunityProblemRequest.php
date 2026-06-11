<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommunityProblemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $problem = $this->route('problem');
        return $this->user()->can('update', $problem);
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'location' => 'sometimes|string|max:255',
            'contact_info' => 'nullable|string|max:255',
            'status_id' => 'sometimes|exists:community_problem_statuses,id',
            'linked_project_id' => 'nullable|exists:projects,id',
            'feedback' => 'nullable|string',
            'rating' => 'nullable|integer|min:1|max:5',
            'research_center_id' => 'nullable|exists:research_centers,id',
        ];
    }
}
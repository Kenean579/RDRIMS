<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommunityProblemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'location'    => 'required|string|max:255',
            'contact_info'=> 'nullable|string|max:255',
            'is_anonymous'=> 'nullable|boolean',
            'status_id'   => 'nullable|exists:community_problem_statuses,id',
            'linked_project_id' => 'nullable|exists:projects,id',
            'results_summary' => 'nullable|string',
            'rating'      => 'nullable|integer|min:1|max:5',
        ];
    }
}
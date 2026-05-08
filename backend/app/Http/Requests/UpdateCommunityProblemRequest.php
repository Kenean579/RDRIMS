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
            'title'       => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'location'    => 'sometimes|string|max:255',
            'contact_info'=> 'nullable|string|max:255',
            'is_anonymous'=> 'nullable|boolean',
            'status_id'   => 'nullable|exists:community_problem_statuses,id',
        ];
    }
}
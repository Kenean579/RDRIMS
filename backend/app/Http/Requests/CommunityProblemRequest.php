<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommunityProblemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:500',
            'description' => 'required|string',
            'location' => 'required|string',
            'reported_by' => 'required|string',
            'status' => 'required|in:open,investigating,solved,closed',
            'academic_year_id' => 'required|exists:academic_years,id',
        ];
    }
}

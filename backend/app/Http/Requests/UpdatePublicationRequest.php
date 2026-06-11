<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePublicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => 'sometimes|exists:projects,id',
            'title' => 'sometimes|string|max:255',
            'abstract' => 'nullable|string',
            'keywords' => 'nullable|string',
            'journal' => 'sometimes|string|max:255',
            'doi' => 'nullable|string|max:255',
            'scholar_url' => 'nullable|string|max:255',
            'publication_date' => 'nullable|date',
            'citation_count' => 'nullable|integer|min:0',
            'file_id' => 'nullable|exists:files,id',
            'research_center_id' => 'nullable|exists:research_centers,id',
        ];
    }
}
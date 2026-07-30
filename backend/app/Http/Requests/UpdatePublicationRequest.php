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
            'type_id' => 'sometimes|exists:publication_types,id',
            'title' => 'sometimes|string|max:255',
            'abstract' => 'nullable|string',
            'keywords' => 'nullable|string',
            'journal' => 'sometimes|string|max:255',
            'volume' => 'nullable|string|max:50',
            'issue' => 'nullable|string|max:50',
            'pages' => 'nullable|string|max:50',
            'publisher' => 'nullable|string|max:255',
            'conference_name' => 'nullable|string|max:255',
            'doi' => 'nullable|string|max:255',
            'isbn' => 'nullable|string|max:50',
            'issn' => 'nullable|string|max:50',
            'scholar_url' => 'nullable|url|max:255',
            'publication_date' => 'nullable|date',
            'citation_count' => 'nullable|integer|min:0',
            'file_id' => 'nullable|exists:files,id',
            'research_center_id' => 'nullable|exists:research_centers,id',
        ];
    }
}
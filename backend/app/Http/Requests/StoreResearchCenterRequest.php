<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ResearchCenter;

class StoreResearchCenterRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('create', ResearchCenter::class); }

    public function rules(): array
    {
        return [
            'name'                => 'required|string|max:255',
            'code'                => 'required|string|max:50|unique:research_centers,code',
            'director_id'         => 'nullable|exists:users,id',
            'logo_file_id'        => 'nullable|exists:files,id',
            'parent_university_id'=> 'nullable|exists:universities,id',
            'parent_campus_id'    => 'nullable|exists:campuses,id',
            'parent_faculty_id'   => 'nullable|exists:faculties,id',
            'description'         => 'nullable|string',
        ];
    }
}

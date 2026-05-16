<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'report_type_id' => 'required|exists:report_types,id',
            'submission_date' => 'required|date',
            'file_id' => 'nullable|exists:files,id',
        ];
    }
}

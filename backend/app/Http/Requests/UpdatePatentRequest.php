<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $patent = $this->route('patent');
        return $this->user()->can('update', $patent);
    }

    public function rules(): array
    {
        return [
            'project_id'    => 'nullable|exists:projects,id',
            'title'         => 'sometimes|string|max:255',
            'inventors'     => 'sometimes|string',
            'filing_date'   => 'sometimes|date',
            'patent_number' => 'nullable|string|max:100',
            'status_id'     => 'nullable|exists:patent_statuses,id',
        ];
    }
}
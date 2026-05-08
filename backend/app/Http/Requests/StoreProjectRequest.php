<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Project::class);
    }

    public function rules(): array
    {
        return [
            'proposal_id'      => 'required|exists:proposals,id',
            'title'            => 'sometimes|string|max:255',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after:start_date',
            'total_budget'     => 'required|numeric|min:0',
            'budget_allocation'=> 'nullable|array',
            'pi_id'            => 'required|exists:users,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
        ];
    }
}
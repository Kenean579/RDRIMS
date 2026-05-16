<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'proposal_id'       => 'nullable|exists:proposals,id',
            'title'             => 'required|string|max:500',
            'total_budget'      => 'required|numeric|min:0',
            'budget_allocation' => 'nullable|json',
            'cover_image_id'    => 'nullable|exists:files,id',
            'pi_id'             => 'required|exists:users,id',
            'academic_year_id'  => 'required|exists:academic_years,id',
            'start_date'        => 'nullable|date',
            'end_date'          => 'nullable|date|after_or_equal:start_date',
        ];
    }
}
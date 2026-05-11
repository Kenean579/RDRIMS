<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMoURequest extends FormRequest
{
    public function authorize(): bool
    {
        $partner = $this->route('partner');
        return $this->user()->can('update', $partner);
    }

    public function rules(): array
    {
        return [
            'start_date' => 'sometimes|date',
            'end_date'   => 'sometimes|date|after_or_equal:start_date',
        ];
    }
}
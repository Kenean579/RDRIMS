<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMoURequest extends FormRequest
{
    public function authorize(): bool
    {
        $partner = $this->route('partner');
        return $this->user()->can('update', $partner);
    }

    public function rules(): array
    {
        return [
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ];
    }
}
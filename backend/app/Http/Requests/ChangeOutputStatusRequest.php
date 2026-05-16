<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangeOutputStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $output = $this->route('output');
        return $this->user()->can('changeStatus', $output);
    }

    public function rules(): array
    {
        return [
            'status_id' => 'required|exists:output_statuses,id',
        ];
    }
}

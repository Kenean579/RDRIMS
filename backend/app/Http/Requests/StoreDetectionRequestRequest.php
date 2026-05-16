<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDetectionRequestRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'detectable_type' => 'required|string',
            'detectable_id'   => 'required|integer',
            'service_id'      => 'required|exists:detection_services,id',
            'file_id'         => 'required|exists:files,id',
        ];
    }
}

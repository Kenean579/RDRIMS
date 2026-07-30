<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Models\User;

class AddInvestigatorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled in controller via policy
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string|in:co_pi,member',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $project = $this->route('project');
            $userId = $this->input('user_id');
            
            if ($project && $userId) {
                $user = User::find($userId);
                
                if ($user && $user->university_id !== $project->pi->university_id) {
                    $validator->errors()->add(
                        'user_id',
                        'The investigator must be from the same university as the project PI.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'user_id.exists' => 'The selected user does not exist.',
            'role.in' => 'Role must be either co_pi or member.',
        ];
    }
}

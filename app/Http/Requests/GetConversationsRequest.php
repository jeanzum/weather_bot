<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetConversationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'limit' => 'nullable|integer|min:1|max:50'
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'El ID de usuario es obligatorio.',
            'user_id.exists' => 'El usuario especificado no existe.',
            'limit.integer' => 'El límite debe ser un número entero.',
            'limit.min' => 'El límite debe ser al menos 1.',
            'limit.max' => 'El límite no puede exceder 50.',
        ];
    }
}
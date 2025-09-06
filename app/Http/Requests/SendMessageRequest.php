<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => 'required|string|max:1000',
            'conversation_id' => 'nullable|exists:conversations,id',
            'user_id' => 'required|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'El mensaje es obligatorio.',
            'message.string' => 'El mensaje debe ser texto válido.',
            'message.max' => 'El mensaje no puede exceder 1000 caracteres.',
            'conversation_id.exists' => 'La conversación especificada no existe.',
            'user_id.required' => 'El ID de usuario es obligatorio.',
            'user_id.exists' => 'El usuario especificado no existe.',
        ];
    }
}
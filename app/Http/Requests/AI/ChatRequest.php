<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('ai.use') ?? false;
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $messages = $this->input('messages');
            if (is_array($messages) && ($messages[array_key_last($messages)]['role'] ?? null) !== 'user') {
                $validator->errors()->add('messages', 'La conversation doit se terminer par votre question.');
            }
        }];
    }

    public function rules(): array
    {
        return ['messages' => ['required', 'array', 'min:1', 'max:20'], 'messages.*' => ['required', 'array:role,content'], 'messages.*.role' => ['required', 'in:user,assistant'], 'messages.*.content' => ['required', 'string', 'max:2000']];
    }
}

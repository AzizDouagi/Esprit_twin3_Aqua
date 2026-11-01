<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;

class ChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('ai.use') ?? false;
    }

    public function rules(): array
    {
        return ['messages' => ['required', 'array', 'min:1', 'max:20'], 'messages.*' => ['required', 'array:role,content'], 'messages.*.role' => ['required', 'in:user,assistant'], 'messages.*.content' => ['required', 'string', 'max:2000']];
    }
}

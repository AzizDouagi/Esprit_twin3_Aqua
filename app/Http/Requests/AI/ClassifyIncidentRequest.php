<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;

class ClassifyIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->can('ai.use') && $user->can('incidents.report');
    }

    public function rules(): array
    {
        return ['titre' => ['required', 'string', 'max:180'], 'description' => ['required', 'string', 'min:5', 'max:2500']];
    }
}

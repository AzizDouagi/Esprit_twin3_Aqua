<?php

namespace App\Http\Requests;

use App\Models\Infrastructure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInfrastructureRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Infrastructure::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:150'],
            'type' => ['required', Rule::in(Infrastructure::TYPES)],
            'localisation' => ['required', 'string', 'max:180'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'etat' => ['required', Rule::in(Infrastructure::ETATS)],
            'date_installation' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom de l’infrastructure est obligatoire.',
            'type.required' => 'Le type d’infrastructure est obligatoire.',
            'type.in' => 'Le type sélectionné est invalide.',
            'localisation.required' => 'La localisation est obligatoire.',
            'etat.required' => 'L’état est obligatoire.',
            'etat.in' => 'L’état sélectionné est invalide.',
            'date_installation.date' => 'La date d’installation doit être une date valide.',
            'description.max' => 'La description ne doit pas dépasser :max caractères.',
        ];
    }
}

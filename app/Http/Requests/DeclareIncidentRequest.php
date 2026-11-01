<?php

namespace App\Http\Requests;

use App\Models\Incident;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeclareIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Incident::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'titre' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'min:10', 'max:2500'],
            'type' => ['required', Rule::in(Incident::TYPES)],
            'gravite' => ['required', Rule::in(Incident::GRAVITES)],
            'infrastructure_id' => ['required', Rule::exists('infrastructures', 'id')->whereNull('deleted_at')],
            'photos' => ['nullable', 'array', 'max:3'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    public function messages(): array
    {
        return [
            'titre.required' => 'Donnez un titre à votre signalement.',
            'description.required' => 'Décrivez le problème observé.',
            'description.min' => 'La description doit contenir au moins 10 caractères.',
            'type.required' => 'Choisissez un type d’incident.',
            'gravite.required' => 'Indiquez la gravité estimée.',
            'infrastructure_id.required' => 'Sélectionnez l’infrastructure concernée.',
            'infrastructure_id.exists' => 'L’infrastructure sélectionnée est introuvable.',
            'photos.*.image' => 'Les photos doivent être des images.',
            'photos.*.max' => 'Chaque photo ne doit pas dépasser 4 Mo.',
        ];
    }
}

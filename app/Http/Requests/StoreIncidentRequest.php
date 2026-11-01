<?php

namespace App\Http\Requests;

use App\Models\Incident;
use App\Rules\ActiveTechnician;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIncidentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Incident::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'titre' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:2500'],
            'type' => ['required', Rule::in(Incident::TYPES)],
            'gravite' => ['required', Rule::in(Incident::GRAVITES)],
            'statut' => ['required', Rule::in($this->user()->can('incidents.manage') ? Incident::STATUTS : ['Ouvert'])],
            'date_incident' => ['required', 'date'],
            'infrastructure_id' => ['required', Rule::exists('infrastructures', 'id')->whereNull('deleted_at')],
            'technicien_id' => $this->user()->can('incidents.manage') ? ['nullable', 'integer', new ActiveTechnician] : ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'titre.required' => 'Le titre de l’incident est obligatoire.',
            'description.required' => 'La description de l’incident est obligatoire.',
            'type.required' => 'Le type d’incident est obligatoire.',
            'type.in' => 'Le type d’incident sélectionné est invalide.',
            'gravite.required' => 'La gravité est obligatoire.',
            'gravite.in' => 'La gravité sélectionnée est invalide.',
            'statut.required' => 'Le statut est obligatoire.',
            'statut.in' => 'Le statut sélectionné est invalide.',
            'date_incident.required' => 'La date de l’incident est obligatoire.',
            'date_incident.date' => 'La date de l’incident doit être valide.',
            'infrastructure_id.required' => 'L’infrastructure liée est obligatoire.',
            'infrastructure_id.exists' => 'L’infrastructure sélectionnée est introuvable.',
        ];
    }
}

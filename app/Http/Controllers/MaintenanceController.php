<?php

namespace App\Http\Controllers;

use App\Models\Infrastructure;
use App\Models\Maintenance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    public function index(Request $request): View
    {
        $maintenances = Maintenance::with(['infrastructure', 'technicien'])
            ->when($request->string('statut')->toString(), fn ($q, $v) => $q->where('statut', $v))
            ->when($request->string('infrastructure_id')->toString(), fn ($q, $v) => $q->where('infrastructure_id', $v))
            ->when($request->string('technicien_id')->toString(), fn ($q, $v) => $q->where('technicien_id', $v))
            ->when($request->string('mois')->toString(), fn ($q, $v) => $q->whereBetween('date_prevue', [
                Carbon::parse($v.'-01')->startOfMonth(), Carbon::parse($v.'-01')->endOfMonth(),
            ]))
            ->orderBy('date_prevue')
            ->paginate(12)
            ->withQueryString();

        return view('maintenances.index', [
            'maintenances' => $maintenances,
            'infrastructures' => Infrastructure::orderBy('nom')->get(),
            'techniciens' => User::permission('maintenances.manage')->orderBy('name')->get(),
            'statuts' => Maintenance::STATUTS,
            'coutTotal' => Maintenance::where('statut', 'Réalisée')->sum('cout'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'infrastructure_id' => ['required', 'exists:infrastructures,id'],
            'technicien_id' => ['nullable', 'exists:users,id'],
            'titre' => ['required', 'string', 'max:180'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'date_prevue' => ['required', 'date'],
        ]);

        Maintenance::create($data + ['statut' => 'Planifiée']);

        return back()->with('success', 'Maintenance planifiée.');
    }

    public function update(Request $request, Maintenance $maintenance): RedirectResponse
    {
        $data = $request->validate([
            'statut' => ['required', Rule::in(Maintenance::STATUTS)],
            'date_realisee' => ['nullable', 'date'],
            'cout' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($data['statut'] === 'Réalisée') {
            $data['date_realisee'] ??= today()->toDateString();
        }

        $maintenance->update($data);

        return back()->with('success', 'Maintenance mise à jour.');
    }

    public function destroy(Maintenance $maintenance): RedirectResponse
    {
        $maintenance->delete();

        return back()->with('success', 'Maintenance supprimée.');
    }
}

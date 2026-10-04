<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\Infrastructure;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard.index', [
            'totalInfrastructures' => Infrastructure::count(),
            'totalIncidents' => Incident::count(),
            'incidentsOuverts' => Incident::where('statut', 'Ouvert')->count(),
            'incidentsCritiques' => Incident::where('gravite', 'Critique')->count(),
            'derniersIncidents' => Incident::with('infrastructure')
                ->latest('date_incident')
                ->latest()
                ->take(6)
                ->get(),
        ]);
    }
}

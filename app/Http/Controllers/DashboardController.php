<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\Infrastructure;
use App\Services\DashboardStats;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(DashboardStats $stats): View
    {
        return view('dashboard.index', [
            'totalInfrastructures' => Infrastructure::count(),
            'totalIncidents' => Incident::count(),
            'incidentsOuverts' => Incident::where('statut', 'Ouvert')->count(),
            'incidentsCritiques' => Incident::where('gravite', 'Critique')->count(),
            'mttr' => $stats->mttrHeures(),
            'parGravite' => $stats->parGravite(),
            'parMois' => $stats->parMois(),
            'topRisque' => $stats->topRisque(),
            'derniersIncidents' => Incident::with('infrastructure')
                ->latest('date_incident')
                ->latest()
                ->take(6)
                ->get(),
        ]);
    }
}

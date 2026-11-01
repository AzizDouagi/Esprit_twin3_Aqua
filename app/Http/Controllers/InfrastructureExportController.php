<?php

namespace App\Http\Controllers;

use App\Models\Infrastructure;
use App\Services\MesureService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InfrastructureExportController extends Controller
{
    public function csv(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', Infrastructure::class);

        $infrastructures = $this->liste($request);

        return response()->streamDownload(function () use ($infrastructures): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8 pour Excel
            fputcsv($out, ['ID', 'Nom', 'Type', 'Localisation', 'Latitude', 'Longitude', 'État', 'Installation', 'Incidents'], ';');

            foreach ($infrastructures as $i) {
                fputcsv($out, [$i->id, $i->nom, $i->type, $i->localisation, $i->latitude, $i->longitude, $i->etat, $i->date_installation?->format('Y-m-d'), $i->incidents_count], ';');
            }

            fclose($out);
        }, 'infrastructures-'.now()->format('Ymd').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function pdf(Request $request): Response
    {
        $this->authorize('viewAny', Infrastructure::class);

        return Pdf::loadView('pdf.infrastructures', ['infrastructures' => $this->liste($request), 'date' => now()])
            ->setPaper('a4', 'landscape')
            ->download('infrastructures-'.now()->format('Ymd').'.pdf');
    }

    public function fiche(Infrastructure $infrastructure, MesureService $mesures): Response
    {
        $this->authorize('view', $infrastructure);

        $infrastructure->load(['incidents' => fn ($q) => $q->latest('date_incident'), 'maintenances' => fn ($q) => $q->latest('date_prevue')]);

        return Pdf::loadView('pdf.infrastructure', [
            'infrastructure' => $infrastructure,
            'indicateurs' => $mesures->indicateurs($infrastructure),
            'date' => now(),
        ])->download('fiche-infrastructure-'.$infrastructure->id.'.pdf');
    }

    private function liste(Request $request)
    {
        return Infrastructure::withCount('incidents')
            ->when($request->string('search')->toString(), fn ($q, $s) => $q->where(fn ($q) => $q->where('nom', 'like', "%{$s}%")->orWhere('localisation', 'like', "%{$s}%")))
            ->when($request->string('etat')->toString(), fn ($q, $e) => $q->where('etat', $e))
            ->orderBy('nom')
            ->get();
    }
}

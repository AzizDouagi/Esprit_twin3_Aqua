<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IncidentExportController extends Controller
{
    public function csv(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', Incident::class);

        $incidents = $this->liste($request);

        return response()->streamDownload(function () use ($incidents): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['ID', 'Titre', 'Infrastructure', 'Type', 'Gravité', 'Statut', 'Date', 'Technicien', 'Résolu le'], ';');

            foreach ($incidents as $i) {
                fputcsv($out, [$i->id, $i->titre, $i->infrastructure?->nom, $i->type, $i->gravite, $i->statut, $i->date_incident->format('Y-m-d'), $i->technicien?->name, $i->resolu_le?->format('Y-m-d H:i')], ';');
            }

            fclose($out);
        }, 'incidents-'.now()->format('Ymd').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function pdf(Request $request): Response
    {
        $this->authorize('viewAny', Incident::class);

        return Pdf::loadView('pdf.incidents', ['incidents' => $this->liste($request)])
            ->setPaper('a4', 'landscape')
            ->download('incidents-'.now()->format('Ymd').'.pdf');
    }

    private function liste(Request $request)
    {
        return Incident::with(['infrastructure', 'technicien'])
            ->filtrer($request->only(IncidentController::FILTRES))
            ->trier($request->string('tri')->toString(), $request->string('sens')->toString())
            ->get();
    }
}

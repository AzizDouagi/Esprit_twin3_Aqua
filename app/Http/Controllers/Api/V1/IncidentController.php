<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\IncidentController as WebIncidentController;
use App\Http\Requests\IncidentFilterRequest;
use App\Http\Requests\StoreIncidentRequest;
use App\Http\Requests\UpdateIncidentRequest;
use App\Http\Resources\IncidentResource;
use App\Models\Incident;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class IncidentController extends Controller
{
    public function index(IncidentFilterRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Incident::class);

        $incidents = Incident::with(['infrastructure', 'technicien'])
            ->filtrer($request->only(WebIncidentController::FILTRES))
            ->trier($request->string('tri')->toString(), $request->string('sens')->toString())
            ->paginate((int) $request->query('per_page', 15))
            ->withQueryString();

        return IncidentResource::collection($incidents);
    }

    public function store(StoreIncidentRequest $request): JsonResponse
    {
        $incident = Incident::create($request->validated() + ['declarant_id' => $request->user()->id]);

        return (new IncidentResource($incident->load(['infrastructure', 'technicien'])))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Incident $incident): IncidentResource
    {
        $this->authorize('view', $incident);

        return new IncidentResource($incident->load(['infrastructure', 'technicien']));
    }

    public function update(UpdateIncidentRequest $request, Incident $incident): IncidentResource
    {
        $incident->update($request->validated());

        return new IncidentResource($incident->load(['infrastructure', 'technicien']));
    }

    public function destroy(Incident $incident): Response
    {
        $this->authorize('delete', $incident);

        $incident->delete();

        return response()->noContent();
    }
}

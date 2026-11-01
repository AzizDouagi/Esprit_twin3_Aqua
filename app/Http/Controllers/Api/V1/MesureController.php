<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMesureRequest;
use App\Http\Resources\MesureResource;
use App\Models\Infrastructure;
use App\Models\Mesure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class MesureController extends Controller
{
    public function index(Request $request, Infrastructure $infrastructure): AnonymousResourceCollection
    {
        $this->authorize('view', $infrastructure);

        $request->validate([
            'du' => ['nullable', 'date'],
            'au' => ['nullable', 'date'],
            'anomalie' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'between:1,200'],
        ]);

        $mesures = $infrastructure->mesures()
            ->when($request->query('du'), fn ($q, $v) => $q->whereDate('mesure_le', '>=', $v))
            ->when($request->query('au'), fn ($q, $v) => $q->whereDate('mesure_le', '<=', $v))
            ->when($request->has('anomalie'), fn ($q) => $q->where('anomalie', $request->boolean('anomalie')))
            ->latest('mesure_le')
            ->paginate((int) $request->query('per_page', 50));

        return MesureResource::collection($mesures);
    }

    public function store(StoreMesureRequest $request, Infrastructure $infrastructure): JsonResponse
    {
        $mesure = $infrastructure->mesures()->create($request->validated() + ['mesure_le' => now()]);

        return (new MesureResource($mesure))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Mesure $mesure): MesureResource
    {
        $this->authorize('view', $mesure->infrastructure);

        return new MesureResource($mesure);
    }
}

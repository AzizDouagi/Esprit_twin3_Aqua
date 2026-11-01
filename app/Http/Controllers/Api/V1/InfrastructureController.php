<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInfrastructureRequest;
use App\Http\Requests\UpdateInfrastructureRequest;
use App\Http\Resources\InfrastructureResource;
use App\Models\Infrastructure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class InfrastructureController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Infrastructure::class);

        $request->validate([
            'etat' => ['nullable', 'in:'.implode(',', Infrastructure::ETATS)],
            'type' => ['nullable', 'in:'.implode(',', Infrastructure::TYPES)],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ]);

        $infrastructures = Infrastructure::withCount('incidents')
            ->when($request->query('etat'), fn ($q, $v) => $q->where('etat', $v))
            ->when($request->query('type'), fn ($q, $v) => $q->where('type', $v))
            ->when($request->query('search'), fn ($q, $v) => $q->where('nom', 'like', "%{$v}%"))
            ->orderBy('nom')
            ->paginate((int) $request->query('per_page', 15));

        return InfrastructureResource::collection($infrastructures);
    }

    public function store(StoreInfrastructureRequest $request): JsonResponse
    {
        $infrastructure = Infrastructure::create($request->validated());

        return (new InfrastructureResource($infrastructure))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Infrastructure $infrastructure): InfrastructureResource
    {
        $this->authorize('view', $infrastructure);

        return new InfrastructureResource($infrastructure->loadCount('incidents'));
    }

    public function update(UpdateInfrastructureRequest $request, Infrastructure $infrastructure): InfrastructureResource
    {
        $infrastructure->update($request->validated());

        return new InfrastructureResource($infrastructure);
    }

    public function destroy(Infrastructure $infrastructure): Response
    {
        $this->authorize('delete', $infrastructure);

        $infrastructure->delete();

        return response()->noContent();
    }
}

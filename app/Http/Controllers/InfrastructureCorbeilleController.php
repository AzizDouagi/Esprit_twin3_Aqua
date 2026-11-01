<?php

namespace App\Http\Controllers;

use App\Models\Infrastructure;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InfrastructureCorbeilleController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Infrastructure::class);
        $this->authorize('create', Infrastructure::class);

        return view('infrastructures.corbeille', [
            'infrastructures' => Infrastructure::onlyTrashed()->latest('deleted_at')->paginate(10),
        ]);
    }

    public function restore(int $id): RedirectResponse
    {
        $infrastructure = Infrastructure::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $infrastructure);

        $infrastructure->restore();

        return redirect()->route('infrastructures.corbeille')->with('success', 'Infrastructure restaurée.');
    }

    public function forceDelete(int $id): RedirectResponse
    {
        $infrastructure = Infrastructure::onlyTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $infrastructure);

        $infrastructure->forceDelete();

        return redirect()->route('infrastructures.corbeille')->with('success', 'Infrastructure supprimée définitivement.');
    }
}

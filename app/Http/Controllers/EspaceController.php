<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class EspaceController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('espace.index', [
            'user' => $request->user(),
            'incidents' => $request->user()->incidentsDeclares()->with('infrastructure')->latest()->take(5)->get(),
        ]);
    }
}

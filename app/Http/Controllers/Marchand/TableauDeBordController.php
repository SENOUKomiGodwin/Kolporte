<?php

namespace App\Http\Controllers\Marchand;

use App\Http\Controllers\Controller;
use App\Services\StatistiquesMarchand;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TableauDeBordController extends Controller
{
    public function __invoke(Request $request, StatistiquesMarchand $stats): View
    {
        $boutique = $request->user()->boutiques()->first();

        return view('marchand.tableau', [
            'boutique' => $boutique,
            'stats' => $boutique ? $stats->calculer($boutique) : null,
        ]);
    }
}

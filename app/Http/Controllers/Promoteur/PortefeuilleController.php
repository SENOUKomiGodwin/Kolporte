<?php

namespace App\Http\Controllers\Promoteur;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortefeuilleController extends Controller
{
    public function index(Request $request): View
    {
        $portefeuille = $request->user()->portefeuille;

        $transactions = Transaction::query()
            ->where('portefeuille_id', $portefeuille?->id)
            ->with('commande.lignes.article')
            ->latest()
            ->paginate(20);

        $dejaPaye = (float) Transaction::query()
            ->where('portefeuille_id', $portefeuille?->id)
            ->where('type', 'retrait')
            ->where('statut', 'payee')
            ->sum('montant');

        return view('promoteur.portefeuille', [
            'portefeuille' => $portefeuille,
            'transactions' => $transactions,
            'dejaPaye' => $dejaPaye,
        ]);
    }
}

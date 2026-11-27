<?php

namespace App\Http\Controllers\Promoteur;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\DemanderRetrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
            'retraitMinimum' => (float) config('kolporte.retrait_minimum'),
            'demandeEnAttente' => $portefeuille
                ? $portefeuille->demandesRetrait()->where('statut', 'en_attente')->exists()
                : false,
        ]);
    }

    /** Demande de retrait : montant mis de côté immédiatement. */
    public function demander(Request $request, DemanderRetrait $service): RedirectResponse
    {
        $donnees = Validator::make($request->all(), [
            'montant' => ['required', 'numeric', 'min:'.config('kolporte.retrait_minimum')],
            'moyen_paiement' => ['required', 'in:T-Money,Flooz'],
            'numero_compte' => ['required', 'string', 'max:50'],
        ])->validate();

        $service->demander(
            $request->user(),
            (float) $donnees['montant'],
            $donnees['moyen_paiement'],
            $donnees['numero_compte'],
        );

        return redirect()
            ->route('promoteur.portefeuille')
            ->with('statut', 'Votre demande de retrait est enregistrée et en cours de traitement.');
    }
}

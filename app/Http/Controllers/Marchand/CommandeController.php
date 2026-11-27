<?php

namespace App\Http\Controllers\Marchand;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use App\Services\AnnulerCommande;
use App\Services\ConfirmerLivraison;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CommandeController extends Controller
{
    private const STATUTS = ['en_attente_paiement', 'payee', 'livree', 'annulee'];

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Commande::class);

        $boutique = $request->user()->boutiques()->first();
        $statut = $request->query('statut');

        $commandes = collect();
        if ($boutique) {
            $commandes = Commande::query()
                ->whereHas('lignes.article', fn ($q) => $q->where('boutique_id', $boutique->id))
                ->with(['lien.promoteur', 'lignes.article'])
                ->when(in_array($statut, self::STATUTS, true), fn ($q) => $q->where('statut', $statut))
                ->latest()
                ->paginate(15)
                ->withQueryString();
        }

        return view('marchand.commandes.index', [
            'commandes' => $commandes,
            'statut' => $statut,
            'statuts' => self::STATUTS,
        ]);
    }

    public function show(Commande $commande): View
    {
        Gate::authorize('view', $commande);

        return view('marchand.commandes.show', [
            'commande' => $commande->load('lien.promoteur', 'lignes.article.boutique', 'transactions'),
        ]);
    }

    public function livrer(Commande $commande, ConfirmerLivraison $service): RedirectResponse
    {
        Gate::authorize('update', $commande);

        $resultat = $service->confirmer($commande);

        return redirect()
            ->route('marchand.commandes.show', $commande)
            ->with('statut', $resultat === 'livree'
                ? 'Livraison confirmée : la commission du promoteur est validée.'
                : 'Cette commande ne peut plus être modifiée.');
    }

    public function annuler(Commande $commande, AnnulerCommande $service): RedirectResponse
    {
        Gate::authorize('update', $commande);

        $resultat = $service->annuler($commande);

        return redirect()
            ->route('marchand.commandes.show', $commande)
            ->with('statut', $resultat === 'annulee'
                ? 'Commande annulée : stock remis et commission supprimée.'
                : 'Cette commande ne peut plus être annulée.');
    }
}

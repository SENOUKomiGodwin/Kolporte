<?php

namespace App\Services;

use App\Models\Boutique;
use App\Models\Commande;
use App\Models\LienPromoteur;
use App\Models\LigneCommande;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/** Chiffres du tableau de bord marchand, calculés sur les commandes payées ou livrées. */
class StatistiquesMarchand
{
    private const STATUTS_VENTES = ['payee', 'livree'];

    /**
     * @return array{
     *     ventes_mois: float,
     *     commandes_mois: int,
     *     commissions_versees: float,
     *     promoteurs_actifs: int,
     *     commandes_a_valider: Collection<int, Commande>,
     *     meilleurs_promoteurs: Collection<int, LienPromoteur>
     * }
     */
    public function calculer(Boutique $boutique): array
    {
        $debutMois = now()->startOfMonth();

        $ventesMois = (float) LigneCommande::query()
            ->join('commandes', 'commandes.id', '=', 'lignes_commande.commande_id')
            ->join('articles', 'articles.id', '=', 'lignes_commande.article_id')
            ->where('articles.boutique_id', $boutique->id)
            ->whereIn('commandes.statut', self::STATUTS_VENTES)
            ->where('commandes.created_at', '>=', $debutMois)
            ->sum(DB::raw('lignes_commande.prix_unitaire * lignes_commande.quantite'));

        $commandesMois = Commande::query()
            ->whereIn('statut', self::STATUTS_VENTES)
            ->where('created_at', '>=', $debutMois)
            ->whereHas('lignes.article', fn ($q) => $q->where('boutique_id', $boutique->id))
            ->count();

        $commissionsVersees = (float) Transaction::query()
            ->where('type', 'commission')
            ->whereIn('statut', ['validee', 'payee'])
            ->whereHas('commande.lignes.article', fn ($q) => $q->where('boutique_id', $boutique->id))
            ->sum('montant');

        $promoteursActifs = User::query()
            ->where('role', 'promoteur')
            ->whereHas('liens', fn ($q) => $q
                ->whereHas('article', fn ($a) => $a->where('boutique_id', $boutique->id))
                ->whereHas('commandes', fn ($c) => $c->whereIn('statut', self::STATUTS_VENTES)))
            ->count();

        $commandesAValider = Commande::query()
            ->where('statut', 'payee')
            ->whereHas('lignes.article', fn ($q) => $q->where('boutique_id', $boutique->id))
            ->with(['lien.promoteur', 'lignes.article'])
            ->latest()
            ->limit(5)
            ->get();

        $meilleursPromoteurs = LienPromoteur::query()
            ->whereHas('article', fn ($q) => $q->where('boutique_id', $boutique->id))
            ->with('promoteur')
            ->withCount(['commandes as ventes' => fn ($q) => $q->whereIn('statut', self::STATUTS_VENTES)])
            ->orderByDesc('ventes')
            ->get()
            ->filter(fn (LienPromoteur $lien) => $lien->ventes > 0)
            ->take(5)
            ->values();

        return [
            'ventes_mois' => $ventesMois,
            'commandes_mois' => $commandesMois,
            'commissions_versees' => $commissionsVersees,
            'promoteurs_actifs' => $promoteursActifs,
            'commandes_a_valider' => $commandesAValider,
            'meilleurs_promoteurs' => $meilleursPromoteurs,
        ];
    }
}

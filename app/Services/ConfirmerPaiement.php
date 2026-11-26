<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Commande;
use App\Models\Portefeuille;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

/**
 * Confirme un paiement de façon idempotente : passe la commande à « payee »,
 * décrémente le stock et met la commission du promoteur en attente.
 */
class ConfirmerPaiement
{
    /** @return string 'payee' | 'deja_payee' | 'annulee' | 'stock_insuffisant' */
    public function confirmer(Commande $commande): string
    {
        return DB::transaction(function () use ($commande) {
            $c = Commande::whereKey($commande->id)->lockForUpdate()->firstOrFail();

            if (in_array($c->statut, ['payee', 'livree'], true)) {
                return 'deja_payee';
            }

            if ($c->statut === 'annulee') {
                return 'annulee';
            }

            $lignes = $c->lignes()->with('article')->get();
            $articles = Article::whereIn('id', $lignes->pluck('article_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Quantité totale par article (une commande = un article aujourd'hui)
            $quantites = $lignes->groupBy('article_id')->map(fn ($g) => $g->sum('quantite'));

            foreach ($quantites as $articleId => $quantite) {
                if ((int) ($articles[$articleId]->stock ?? 0) < $quantite) {
                    $c->update(['statut' => 'annulee']);

                    return 'stock_insuffisant';
                }
            }

            foreach ($quantites as $articleId => $quantite) {
                $articles[$articleId]->decrement('stock', $quantite);
            }

            $c->update(['statut' => 'payee']);

            $commission = (float) $lignes->sum('commission');

            if ($commission > 0 && $c->lien_id) {
                $promoteurId = $c->lien?->promoteur_id;
                $portefeuille = $promoteurId
                    ? Portefeuille::where('utilisateur_id', $promoteurId)->lockForUpdate()->first()
                    : null;

                if ($portefeuille) {
                    Transaction::create([
                        'portefeuille_id' => $portefeuille->id,
                        'commande_id' => $c->id,
                        'type' => 'commission',
                        'montant' => $commission,
                        'statut' => 'en_attente',
                    ]);

                    $portefeuille->increment('solde_en_attente', $commission);
                }
            }

            return 'payee';
        });
    }
}

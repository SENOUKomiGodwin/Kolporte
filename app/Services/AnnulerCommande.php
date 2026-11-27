<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Commande;
use App\Models\Portefeuille;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

/**
 * Le marchand annule une commande payée avant livraison : retour en stock,
 * suppression de la commission en attente et décrément du solde en attente.
 * Idempotent : un second appel ne fait rien.
 */
class AnnulerCommande
{
    /** @return string 'annulee' | 'ignore' */
    public function annuler(Commande $commande): string
    {
        return DB::transaction(function () use ($commande) {
            $c = Commande::whereKey($commande->id)->lockForUpdate()->firstOrFail();

            if ($c->statut !== 'payee') {
                return 'ignore';
            }

            // Remise en stock
            $articles = Article::whereIn('id', $c->lignes()->pluck('article_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($c->lignes as $ligne) {
                $articles[$ligne->article_id]?->increment('stock', $ligne->quantite);
            }

            // Suppression des commissions en attente et décrément des soldes
            $transactions = Transaction::query()
                ->where('commande_id', $c->id)
                ->where('type', 'commission')
                ->where('statut', 'en_attente')
                ->lockForUpdate()
                ->get();

            foreach ($transactions as $transaction) {
                $this->retirerDuSoldeEnAttente($transaction);
                $transaction->delete();
            }

            $c->update(['statut' => 'annulee']);

            return 'annulee';
        });
    }

    /** Décrémente solde_en_attente sans jamais le rendre négatif. */
    private function retirerDuSoldeEnAttente(Transaction $transaction): void
    {
        $portefeuille = Portefeuille::whereKey($transaction->portefeuille_id)
            ->lockForUpdate()
            ->first();

        if (! $portefeuille) {
            return;
        }

        $montant = (float) $transaction->montant;
        $enAttente = (float) $portefeuille->solde_en_attente;

        $portefeuille->update([
            'solde_en_attente' => max(0.0, $enAttente - $montant),
        ]);
    }
}

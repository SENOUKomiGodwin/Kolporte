<?php

namespace App\Services;

use App\Models\Commande;
use App\Models\Portefeuille;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

/**
 * Le marchand confirme la livraison : la commande passe à « livree » et les
 * commissions en attente deviennent « validee » (soldes déplacés).
 * Idempotent : un second appel ne fait rien.
 */
class ConfirmerLivraison
{
    /** @return string 'livree' | 'ignore' (statut incompatible ou déjà traitée) */
    public function confirmer(Commande $commande): string
    {
        return DB::transaction(function () use ($commande) {
            $c = Commande::whereKey($commande->id)->lockForUpdate()->firstOrFail();

            if ($c->statut !== 'payee') {
                return 'ignore';
            }

            $c->update(['statut' => 'livree']);

            $transactions = Transaction::query()
                ->where('commande_id', $c->id)
                ->where('type', 'commission')
                ->where('statut', 'en_attente')
                ->lockForUpdate()
                ->get();

            foreach ($transactions as $transaction) {
                $transaction->update(['statut' => 'validee']);
                $this->deplacerVersDisponible($transaction);
            }

            return 'livree';
        });
    }

    /**
     * Déplace le montant de solde_en_attente vers solde_disponible.
     * Garde-fou : un solde ne peut jamais devenir négatif.
     */
    private function deplacerVersDisponible(Transaction $transaction): void
    {
        $portefeuille = Portefeuille::whereKey($transaction->portefeuille_id)
            ->lockForUpdate()
            ->first();

        if (! $portefeuille) {
            return;
        }

        $montant = (float) $transaction->montant;
        $enAttente = (float) $portefeuille->solde_en_attente;

        // Si les données sont incohérentes, on ne prélève jamais plus que le disponible en attente
        $aDeplacer = min($montant, max(0.0, $enAttente));

        $portefeuille->update([
            'solde_en_attente' => $enAttente - $aDeplacer,
            'solde_disponible' => (float) $portefeuille->solde_disponible + $aDeplacer,
        ]);
    }
}

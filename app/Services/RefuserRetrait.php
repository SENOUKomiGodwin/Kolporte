<?php

namespace App\Services;

use App\Models\DemandeRetrait;
use App\Models\Portefeuille;
use Illuminate\Support\Facades\DB;

/**
 * L'admin refuse un retrait : la demande passe à « refusee », le montant est
 * recrédité à solde_disponible et la transaction retrait est supprimée.
 * Idempotent : une demande déjà traitée ne change plus.
 */
class RefuserRetrait
{
    /** @return string 'refusee' | 'ignore' */
    public function refuser(DemandeRetrait $demande): string
    {
        return DB::transaction(function () use ($demande) {
            $d = DemandeRetrait::whereKey($demande->id)->lockForUpdate()->firstOrFail();

            if ($d->statut !== 'en_attente') {
                return 'ignore';
            }

            $d->transaction()->delete();

            $portefeuille = Portefeuille::whereKey($d->portefeuille_id)->lockForUpdate()->first();
            $portefeuille?->increment('solde_disponible', (float) $d->montant);

            $d->update(['statut' => 'refusee']);

            return 'refusee';
        });
    }
}

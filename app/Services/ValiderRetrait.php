<?php

namespace App\Services;

use App\Models\DemandeRetrait;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * L'admin valide un retrait : la demande passe à « validee » et la transaction
 * retrait à « payee ». Le virement Mobile Money est fait hors application.
 * Idempotent : une demande déjà traitée ne change plus.
 */
class ValiderRetrait
{
    /** @return string 'validee' | 'ignore' */
    public function valider(DemandeRetrait $demande, User $admin): string
    {
        return DB::transaction(function () use ($demande, $admin) {
            $d = DemandeRetrait::whereKey($demande->id)->lockForUpdate()->firstOrFail();

            if ($d->statut !== 'en_attente') {
                return 'ignore';
            }

            $d->update([
                'statut' => 'validee',
                'validee_par' => $admin->id,
            ]);

            $d->transaction()->where('statut', 'en_attente')->update(['statut' => 'payee']);

            return 'validee';
        });
    }
}

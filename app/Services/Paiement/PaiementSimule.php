<?php

namespace App\Services\Paiement;

use App\Models\Commande;
use App\Services\ConfirmerPaiement;
use Illuminate\Support\Str;

/** Paiement factice pour les tests et la démo : page locale avec Payer/Échouer. */
class PaiementSimule implements PaiementService
{
    public function __construct(private ConfirmerPaiement $confirmerPaiement) {}

    public function initier(Commande $commande): string
    {
        if (! $commande->reference_paiement) {
            $commande->update(['reference_paiement' => 'SIM-'.$commande->id.'-'.Str::upper(Str::random(6))]);
        }

        return route('commande.paiement', $commande);
    }

    public function confirmer(string $reference): ?Commande
    {
        $commande = Commande::where('reference_paiement', $reference)->first();

        if (! $commande) {
            return null;
        }

        $this->confirmerPaiement->confirmer($commande);

        return $commande->fresh();
    }
}

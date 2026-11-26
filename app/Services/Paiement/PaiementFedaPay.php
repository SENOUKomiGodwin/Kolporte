<?php

namespace App\Services\Paiement;

use App\Models\Commande;
use App\Services\ConfirmerPaiement;
use Illuminate\Support\Str;

/** Paiement Mobile Money via FedaPay (sandbox ou live selon FEDAPAY_ENV). */
class PaiementFedaPay implements PaiementService
{
    public function __construct(
        private FedaPayClient $client,
        private ConfirmerPaiement $confirmerPaiement,
    ) {}

    public function initier(Commande $commande): string
    {
        if (! $commande->reference_paiement) {
            $commande->update(['reference_paiement' => 'KP-'.Str::upper(Str::random(10))]);
        }

        $transaction = $this->client->creerTransaction([
            'amount' => (int) round((float) $commande->montant_total),
            'currency' => ['iso' => 'XOF'],
            'description' => 'Commande Kolporte '.$commande->reference_paiement,
            'callback_url' => route('paiement.fedapay.retour').'?ref='.$commande->reference_paiement,
            'reference' => $commande->reference_paiement,
        ]);

        return $transaction->payment_url;
    }

    public function confirmer(string $reference): ?Commande
    {
        // $reference = id de la transaction FedaPay (webhook) — on relit l'état réel
        $transaction = $this->client->recupererTransaction((int) $reference);

        if (! $transaction || ! $transaction->wasPaid()) {
            return null;
        }

        $commande = Commande::where('reference_paiement', $transaction->reference)->first();

        if (! $commande) {
            return null;
        }

        $this->confirmerPaiement->confirmer($commande);

        return $commande->fresh();
    }
}

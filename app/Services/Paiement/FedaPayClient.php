<?php

namespace App\Services\Paiement;

use FedaPay\FedaPay;
use FedaPay\Transaction;
use FedaPay\Webhook;

/** Client FedaPay encapsulé pour rester testable sans appel réseau. */
class FedaPayClient
{
    public function __construct()
    {
        FedaPay::setApiKey((string) config('kolporte.fedapay.secret'));
        FedaPay::setEnvironment(config('kolporte.fedapay.env') === 'live' ? 'live' : 'sandbox');
    }

    /** Crée une transaction FedaPay et renvoie l'objet (payment_url, id…). */
    public function creerTransaction(array $params): object
    {
        return Transaction::create($params);
    }

    /** Relit une transaction par son id FedaPay. */
    public function recupererTransaction(int $id): ?object
    {
        try {
            return Transaction::retrieve($id);
        } catch (\Throwable) {
            return null;
        }
    }

    /** Relit une transaction par notre référence métier. */
    public function recupererParReference(string $reference): ?object
    {
        try {
            $resultat = Transaction::search(['reference' => $reference]);
            $transactions = $resultat->data ?? [];

            return $transactions[0] ?? null;
        } catch (\Throwable) {
            return null;
        }
    }

    /** Vérifie la signature du webhook et retourne l'événement FedaPay. */
    public function verifierWebhook(string $payload, ?string $signature): object
    {
        return Webhook::constructEvent(
            $payload,
            (string) $signature,
            (string) config('kolporte.fedapay.secret'),
        );
    }
}

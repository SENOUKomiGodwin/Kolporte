<?php

namespace Tests\Fakes;

use App\Services\Paiement\FedaPayClient;

/** Fausse FedaPayClient : aucun appel réseau, comportement scriptable. */
class FakeFedaPayClient extends FedaPayClient
{
    /** @var array<int, array> */
    public array $transactionsCreees = [];

    public ?object $transactionRetour = null;

    public ?object $evenementWebhook = null;

    public bool $signatureValide = true;

    public function __construct() {}

    public function creerTransaction(array $params): object
    {
        $this->transactionsCreees[] = $params;

        return (object) [
            'id' => 42,
            'payment_url' => 'https://sandbox.fedapay.com/pay/42',
        ];
    }

    public function recupererTransaction(int $id): ?object
    {
        return $this->transactionRetour;
    }

    public function recupererParReference(string $reference): ?object
    {
        return $this->transactionRetour;
    }

    public function verifierWebhook(string $payload, ?string $signature): object
    {
        if (! $this->signatureValide) {
            throw new \RuntimeException('Signature invalide');
        }

        return $this->evenementWebhook;
    }
}

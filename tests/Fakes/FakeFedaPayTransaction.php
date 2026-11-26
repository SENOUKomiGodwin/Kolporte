<?php

namespace Tests\Fakes;

/** Transaction FedaPay factice (statuts alignés sur Transaction::wasPaid()). */
class FakeFedaPayTransaction
{
    public function __construct(
        public ?string $reference,
        public string $statut = 'approved',
    ) {}

    public function wasPaid(): bool
    {
        return in_array($this->statut, ['approved', 'transferred', 'refunded'], true);
    }
}

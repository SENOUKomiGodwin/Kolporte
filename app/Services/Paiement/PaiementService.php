<?php

namespace App\Services\Paiement;

use App\Models\Commande;

interface PaiementService
{
    /** Démarre le paiement et renvoie l'URL (ou le chemin) vers lequel rediriger l'acheteur. */
    public function initier(Commande $commande): string;

    /**
     * Confirme le paiement à partir de la référence du prestataire
     * (id de transaction FedaPay, ou reference_paiement en mode simulé).
     * Retourne la commande confirmée ou null si introuvable/non payée.
     */
    public function confirmer(string $reference): ?Commande;
}

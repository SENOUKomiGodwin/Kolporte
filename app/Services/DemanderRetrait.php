<?php

namespace App\Services;

use App\Models\DemandeRetrait;
use App\Models\Portefeuille;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Le promoteur demande un retrait : le montant est mis de côté
 * (sorti de solde_disponible) et une transaction retrait en_attente est créée.
 */
class DemanderRetrait
{
    /**
     * @throws ValidationException si une demande est déjà en attente ou fonds insuffisants
     */
    public function demander(User $promoteur, float $montant, string $moyen, string $numeroCompte): DemandeRetrait
    {
        return DB::transaction(function () use ($promoteur, $montant, $moyen, $numeroCompte) {
            $portefeuille = Portefeuille::where('utilisateur_id', $promoteur->id)
                ->lockForUpdate()
                ->firstOrFail();

            $dejaEnAttente = DemandeRetrait::query()
                ->where('portefeuille_id', $portefeuille->id)
                ->where('statut', 'en_attente')
                ->exists();

            if ($dejaEnAttente) {
                throw ValidationException::withMessages([
                    'montant' => 'Vous avez déjà un retrait en attente de traitement.',
                ]);
            }

            $disponible = (float) $portefeuille->solde_disponible;
            if ($disponible < $montant) {
                throw ValidationException::withMessages([
                    'montant' => 'Le montant dépasse votre solde disponible.',
                ]);
            }

            $demande = DemandeRetrait::create([
                'portefeuille_id' => $portefeuille->id,
                'montant' => $montant,
                'moyen_paiement' => $moyen,
                'numero_compte' => $numeroCompte,
                'statut' => 'en_attente',
            ]);

            // Le montant est mis de côté : sorti du disponible, jamais négatif
            $portefeuille->decrement('solde_disponible', $montant);

            Transaction::create([
                'portefeuille_id' => $portefeuille->id,
                'demande_retrait_id' => $demande->id,
                'type' => 'retrait',
                'montant' => $montant, // toujours positif ; le signe − n'existe qu'à l'affichage
                'statut' => 'en_attente',
            ]);

            return $demande;
        });
    }
}

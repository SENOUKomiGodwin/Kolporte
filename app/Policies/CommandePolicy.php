<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\Commande;
use App\Models\User;

class CommandePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'marchand';
    }

    public function view(User $user, Commande $commande): bool
    {
        return $this->possede($user, $commande);
    }

    /** Confirmer la livraison ou annuler : réservé au marchand propriétaire. */
    public function update(User $user, Commande $commande): bool
    {
        return $this->possede($user, $commande);
    }

    /** La commande contient-elle au moins un article d'une boutique du marchand ? */
    private function possede(User $user, Commande $commande): bool
    {
        $boutiqueIds = Article::query()
            ->whereIn('id', $commande->lignes()->pluck('article_id'))
            ->pluck('boutique_id');

        return $user->boutiques()->whereIn('id', $boutiqueIds)->exists();
    }
}

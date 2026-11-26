<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Commande extends Model
{
    protected $table = 'commandes';

    protected $fillable = [
        'lien_id', 'nom_acheteur', 'telephone_acheteur',
        'adresse_livraison', 'montant_total', 'statut', 'reference_paiement',
    ];

    protected $casts = ['montant_total' => 'decimal:2'];

    public function lien(): BelongsTo
    {
        return $this->belongsTo(LienPromoteur::class, 'lien_id');
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(LigneCommande::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}

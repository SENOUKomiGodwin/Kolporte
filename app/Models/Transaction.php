<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $table = 'transactions';

    protected $fillable = ['portefeuille_id', 'commande_id', 'demande_retrait_id', 'type', 'montant', 'statut'];

    protected $casts = ['montant' => 'decimal:2'];

    public function portefeuille(): BelongsTo
    {
        return $this->belongsTo(Portefeuille::class);
    }

    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class);
    }

    public function demandeRetrait(): BelongsTo
    {
        return $this->belongsTo(DemandeRetrait::class);
    }
}

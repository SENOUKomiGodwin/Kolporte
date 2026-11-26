<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemandeRetrait extends Model
{
    protected $table = 'demandes_retrait';

    protected $fillable = ['portefeuille_id', 'montant', 'moyen_paiement', 'numero_compte', 'statut', 'validee_par'];

    protected $casts = ['montant' => 'decimal:2'];

    public function portefeuille(): BelongsTo
    {
        return $this->belongsTo(Portefeuille::class);
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validee_par');
    }
}

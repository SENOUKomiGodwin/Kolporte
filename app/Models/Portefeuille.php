<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Portefeuille extends Model
{
    protected $table = 'portefeuilles';

    protected $fillable = ['utilisateur_id', 'solde_disponible', 'solde_en_attente'];

    protected $casts = [
        'solde_disponible' => 'decimal:2',
        'solde_en_attente' => 'decimal:2',
    ];

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function demandesRetrait(): HasMany
    {
        return $this->hasMany(DemandeRetrait::class);
    }
}

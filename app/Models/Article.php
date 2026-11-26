<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    protected $table = 'articles';

    protected $fillable = [
        'boutique_id', 'titre', 'description', 'prix', 'stock',
        'photo_url', 'type_commission', 'valeur_commission',
    ];

    protected $casts = [
        'prix' => 'decimal:2',
        'valeur_commission' => 'decimal:2',
    ];

    public function boutique(): BelongsTo
    {
        return $this->belongsTo(Boutique::class);
    }

    public function liens(): HasMany
    {
        return $this->hasMany(LienPromoteur::class);
    }

    /** Commission du promoteur pour UNE unité vendue. */
    public function calculerCommission(): float
    {
        $commission = $this->type_commission === 'pourcentage'
            ? (float) $this->prix * (float) $this->valeur_commission / 100
            : (float) $this->valeur_commission;

        return round($commission, 2);
    }
}

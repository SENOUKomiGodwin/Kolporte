<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Clic extends Model
{
    protected $table = 'clics';

    const UPDATED_AT = null; // la table n'a que created_at

    protected $fillable = ['lien_id', 'adresse_ip'];

    public function lien(): BelongsTo
    {
        return $this->belongsTo(LienPromoteur::class, 'lien_id');
    }
}

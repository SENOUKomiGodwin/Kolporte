<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LienPromoteur extends Model
{
    protected $table = 'liens_promoteur';

    protected $fillable = ['promoteur_id', 'article_id', 'code'];

    public function promoteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'promoteur_id');
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function clics(): HasMany
    {
        return $this->hasMany(Clic::class, 'lien_id');
    }

    public function commandes(): HasMany
    {
        return $this->hasMany(Commande::class, 'lien_id');
    }

    /** Code unique à placer dans ?ref=... */
    public static function genererCode(): string
    {
        do {
            $code = 'P' . strtoupper(Str::random(7));
        } while (static::where('code', $code)->exists());

        return $code;
    }
}

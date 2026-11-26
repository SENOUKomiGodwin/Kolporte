<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Boutique extends Model
{
    protected $table = 'boutiques';

    protected $fillable = ['marchand_id', 'nom', 'description', 'logo_url'];

    public function marchand(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marchand_id');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }
}

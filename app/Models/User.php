<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'telephone',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** Chemin de l'espace correspondant au rôle. */
    public function cheminEspace(): string
    {
        return match ($this->role) {
            'marchand' => '/marchand',
            'admin' => '/admin',
            default => '/promoteur',
        };
    }

    public function portefeuille(): HasOne
    {
        return $this->hasOne(Portefeuille::class, 'utilisateur_id');
    }

    public function boutiques(): HasMany
    {
        return $this->hasMany(Boutique::class, 'marchand_id');
    }

    public function liens(): HasMany
    {
        return $this->hasMany(LienPromoteur::class, 'promoteur_id');
    }

    protected static function booted(): void
    {
        static::created(function (User $user): void {
            $user->portefeuille()->create([
                'solde_disponible' => 0,
                'solde_en_attente' => 0,
            ]);
        });
    }
}

<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Boutique;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

// Données de démonstration (mêmes exemples que les maquettes). Mot de passe : « password ».
// Lancer : php artisan db:seed --class=DemoSeeder
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->utilisateur('Administrateur', 'admin@kolporte.test', 'admin');

        foreach (['Koffi A.', 'Ama T.', 'Yao M.'] as $i => $nom) {
            $this->utilisateur($nom, 'promoteur' . ($i + 1) . '@kolporte.test', 'promoteur');
        }

        $catalogue = [
            ['Chez Akossiwa',  'Pagne wax 6 yards',     12000, 'pourcentage', 10],
            ['Atelier Kodjo',  'Sac en cuir artisanal', 18500, 'pourcentage', 8],
            ['Karité du Nord', 'Savon au karité',        2500, 'pourcentage', 15],
            ['Maison Ayélé',   'Boubou brodé',          25000, 'pourcentage', 12],
        ];

        foreach ($catalogue as $i => [$boutiqueNom, $titre, $prix, $type, $valeur]) {
            $marchand = $this->utilisateur('Marchand ' . ($i + 1), 'marchand' . ($i + 1) . '@kolporte.test', 'marchand');
            $boutique = Boutique::create(['marchand_id' => $marchand->id, 'nom' => $boutiqueNom]);
            Article::create([
                'boutique_id' => $boutique->id,
                'titre' => $titre,
                'prix' => $prix,
                'stock' => 15,
                'type_commission' => $type,
                'valeur_commission' => $valeur,
            ]);
        }
    }

    private function utilisateur(string $nom, string $email, string $role): User
    {
        $user = new User();
        $user->forceFill([
            'name' => $nom,
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => $role,
        ])->save();

        return $user;
    }
}

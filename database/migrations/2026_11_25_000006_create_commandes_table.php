<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            // Lien d'origine (cookie ref) : nul si l'acheteur n'est pas passé par un promoteur
            $table->foreignId('lien_id')->nullable()->constrained('liens_promoteur')->nullOnDelete();
            $table->string('nom_acheteur');
            $table->string('telephone_acheteur', 30);
            $table->string('adresse_livraison');
            $table->decimal('montant_total', 12, 2);
            $table->enum('statut', ['en_attente_paiement', 'payee', 'livree', 'annulee'])->default('en_attente_paiement');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};

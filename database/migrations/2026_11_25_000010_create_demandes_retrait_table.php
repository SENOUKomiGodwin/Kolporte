<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandes_retrait', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portefeuille_id')->constrained('portefeuilles')->cascadeOnDelete();
            $table->decimal('montant', 12, 2);
            $table->string('moyen_paiement', 50);   // T-Money, Flooz...
            $table->string('numero_compte', 50);
            $table->enum('statut', ['en_attente', 'validee', 'refusee'])->default('en_attente');
            $table->foreignId('validee_par')->nullable()->constrained('users')->nullOnDelete(); // administrateur
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandes_retrait');
    }
};

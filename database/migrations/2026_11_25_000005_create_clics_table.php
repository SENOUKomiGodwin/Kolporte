<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lien_id')->constrained('liens_promoteur')->cascadeOnDelete();
            $table->string('adresse_ip', 45);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['lien_id', 'adresse_ip', 'created_at']); // anti-fraude : clics répétés
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clics');
    }
};

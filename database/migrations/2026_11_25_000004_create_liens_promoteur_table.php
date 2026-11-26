<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('liens_promoteur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promoteur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->string('code', 20)->unique(); // ex. PROMO456 dans ?ref=PROMO456
            $table->timestamps();

            $table->unique(['promoteur_id', 'article_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liens_promoteur');
    }
};

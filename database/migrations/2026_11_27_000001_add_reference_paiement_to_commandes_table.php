<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            // Référence interne de paiement (SIM-… ou KP-…) renvoyée au prestataire
            $table->string('reference_paiement', 40)->nullable()->after('statut')->index();
        });
    }

    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->dropColumn('reference_paiement');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Lie la transaction de retrait à sa demande (nullable : les commissions n'en ont pas)
            $table->foreignId('demande_retrait_id')->nullable()->after('commande_id')
                ->constrained('demandes_retrait')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('demande_retrait_id');
        });
    }
};

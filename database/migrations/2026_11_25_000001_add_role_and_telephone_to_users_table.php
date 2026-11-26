<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// La table `users` de Laravel joue le rôle de la table « utilisateurs » du schéma.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telephone', 30)->nullable()->unique()->after('email');
            $table->enum('role', ['marchand', 'promoteur', 'admin'])->default('promoteur')->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['telephone']);
            $table->dropColumn(['telephone', 'role']);
        });
    }
};

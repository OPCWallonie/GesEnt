<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('factures_achat', function (Blueprint $table) {
            $table->string('statut', 20)->default('en_attente')->change();
        });
    }

    public function down(): void
    {
        Schema::table('factures_achat', function (Blueprint $table) {
            $table->enum('statut', ['en_attente', 'payee'])->default('en_attente')->change();
        });
    }
};

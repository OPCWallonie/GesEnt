<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('factures_achat', function (Blueprint $table) {
            $table->string('fichier_path', 255)->nullable()->after('notes');
            $table->string('fichier_mime', 50)->nullable()->after('fichier_path');
            $table->string('fichier_nom_original', 255)->nullable()->after('fichier_mime');
        });
    }

    public function down(): void
    {
        Schema::table('factures_achat', function (Blueprint $table) {
            $table->dropColumn(['fichier_path', 'fichier_mime', 'fichier_nom_original']);
        });
    }
};

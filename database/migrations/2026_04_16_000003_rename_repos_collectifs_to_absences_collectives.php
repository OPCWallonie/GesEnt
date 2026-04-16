<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Renommer la table principale
        Schema::rename('repos_collectifs', 'absences_collectives');

        // 2. Ajouter le champ type_collectif (tous les existants = repos_compensatoire)
        Schema::table('absences_collectives', function (Blueprint $table) {
            $table->string('type_collectif', 30)->default('repos_compensatoire')->after('id');
        });

        // 3. Renommer la FK sur absences
        Schema::table('absences', function (Blueprint $table) {
            $table->renameColumn('repos_collectif_id', 'absence_collective_id');
        });
    }

    public function down(): void
    {
        Schema::table('absences', function (Blueprint $table) {
            $table->renameColumn('absence_collective_id', 'repos_collectif_id');
        });

        Schema::table('absences_collectives', function (Blueprint $table) {
            $table->dropColumn('type_collectif');
        });

        Schema::rename('absences_collectives', 'repos_collectifs');
    }
};

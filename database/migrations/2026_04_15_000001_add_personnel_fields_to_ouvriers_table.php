<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ouvriers', function (Blueprint $table) {
            // Type de personnel (ouvrier, employé terrain, admin, direction)
            $table->string('type_personnel', 30)->default('ouvrier')->after('id');

            // Commission paritaire
            $table->string('commission_paritaire', 20)->default('CP124')->after('categorie');

            // Coût mensuel chargé (alternatif au cout_horaire pour employés/direction)
            $table->decimal('cout_mensuel', 10, 2)->nullable()->after('cout_horaire');

            // Motif de départ (alimenté lors de la désactivation)
            $table->string('motif_sortie', 50)->nullable()->after('date_sortie');

            // Index pour filtrage rapide
            $table->index('type_personnel');
            $table->index('commission_paritaire');
        });
    }

    public function down(): void
    {
        Schema::table('ouvriers', function (Blueprint $table) {
            $table->dropIndex(['type_personnel']);
            $table->dropIndex(['commission_paritaire']);
            $table->dropColumn(['type_personnel', 'commission_paritaire', 'cout_mensuel', 'motif_sortie']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parametres_entreprise', function (Blueprint $table) {
            $table->decimal('volatilite_cross_seuil_prix_pct',   5, 2)->default(5.00)->after('volatilite_seuil_ligne_devis_eur');
            $table->decimal('volatilite_cross_seuil_position',   5, 4)->default(0.30)->after('volatilite_cross_seuil_prix_pct');
            $table->decimal('volatilite_cross_seuil_tendance_pp',5, 2)->default(10.00)->after('volatilite_cross_seuil_position');
        });
    }

    public function down(): void
    {
        Schema::table('parametres_entreprise', function (Blueprint $table) {
            $table->dropColumn([
                'volatilite_cross_seuil_prix_pct',
                'volatilite_cross_seuil_position',
                'volatilite_cross_seuil_tendance_pp',
            ]);
        });
    }
};

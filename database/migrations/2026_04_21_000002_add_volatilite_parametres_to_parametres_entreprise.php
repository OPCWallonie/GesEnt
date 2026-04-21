<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parametres_entreprise', function (Blueprint $table) {
            $table->boolean('volatilite_active')->default(true)->after('cle_repartition_frais');
            $table->unsignedSmallInteger('volatilite_fenetre_mois')->default(24)->after('volatilite_active');
            $table->unsignedSmallInteger('volatilite_min_changements_pour_classer')->default(3)->after('volatilite_fenetre_mois');
            $table->decimal('volatilite_seuil_stable_amplitude_pct', 5, 2)->default(2.00)->after('volatilite_min_changements_pour_classer');
            $table->decimal('volatilite_seuil_a_variation_pct', 5, 2)->default(8.00)->after('volatilite_seuil_stable_amplitude_pct');
            $table->unsignedSmallInteger('volatilite_seuil_a_max_changements_anciens')->default(3)->after('volatilite_seuil_a_variation_pct');
            $table->decimal('volatilite_seuil_b_pente_annuelle_pct', 5, 2)->default(10.00)->after('volatilite_seuil_a_max_changements_anciens');
            $table->decimal('volatilite_seuil_b_r2_min', 4, 3)->default(0.700)->after('volatilite_seuil_b_pente_annuelle_pct');
            $table->unsignedSmallInteger('volatilite_seuil_c_nb_changements')->default(4)->after('volatilite_seuil_b_r2_min');
            $table->decimal('volatilite_seuil_c_amplitude_pct', 5, 2)->default(10.00)->after('volatilite_seuil_c_nb_changements');
            $table->decimal('volatilite_garde_fou_absolu_pct', 5, 2)->default(15.00)->after('volatilite_seuil_c_amplitude_pct');
            $table->decimal('volatilite_signal_relatif_ecart_pct', 5, 2)->default(5.00)->after('volatilite_garde_fou_absolu_pct');
            $table->decimal('volatilite_seuil_ligne_devis_eur', 8, 2)->default(200.00)->after('volatilite_signal_relatif_ecart_pct');
        });
    }

    public function down(): void
    {
        Schema::table('parametres_entreprise', function (Blueprint $table) {
            $table->dropColumn([
                'volatilite_active',
                'volatilite_fenetre_mois',
                'volatilite_min_changements_pour_classer',
                'volatilite_seuil_stable_amplitude_pct',
                'volatilite_seuil_a_variation_pct',
                'volatilite_seuil_a_max_changements_anciens',
                'volatilite_seuil_b_pente_annuelle_pct',
                'volatilite_seuil_b_r2_min',
                'volatilite_seuil_c_nb_changements',
                'volatilite_seuil_c_amplitude_pct',
                'volatilite_garde_fou_absolu_pct',
                'volatilite_signal_relatif_ecart_pct',
                'volatilite_seuil_ligne_devis_eur',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalog_produits', function (Blueprint $table) {
            $table->string('volatilite_classe', 20)->nullable()->after('derniere_sync');
            $table->decimal('volatilite_amplitude_pct', 8, 2)->nullable()->after('volatilite_classe');
            $table->decimal('volatilite_tendance_pct', 8, 2)->nullable()->after('volatilite_amplitude_pct');
            $table->decimal('volatilite_position_relative', 5, 4)->nullable()->after('volatilite_tendance_pct');
            $table->unsignedInteger('volatilite_nb_changements')->nullable()->after('volatilite_position_relative');
            $table->boolean('volatilite_signal_relatif')->default(false)->after('volatilite_nb_changements');
            $table->boolean('volatilite_signal_absolu')->default(false)->after('volatilite_signal_relatif');
            $table->string('volatilite_groupe_comparaison', 50)->nullable()->after('volatilite_signal_absolu');
            $table->timestamp('volatilite_calculee_at')->nullable()->after('volatilite_groupe_comparaison');
            $table->string('volatilite_flag_manuel', 20)->default('auto')->after('volatilite_calculee_at');

            $table->index('volatilite_classe', 'idx_catalog_produits_volatilite_classe');
            $table->index('volatilite_calculee_at', 'idx_catalog_produits_volatilite_calculee_at');
        });
    }

    public function down(): void
    {
        Schema::table('catalog_produits', function (Blueprint $table) {
            $table->dropIndex('idx_catalog_produits_volatilite_classe');
            $table->dropIndex('idx_catalog_produits_volatilite_calculee_at');
            $table->dropColumn([
                'volatilite_classe',
                'volatilite_amplitude_pct',
                'volatilite_tendance_pct',
                'volatilite_position_relative',
                'volatilite_nb_changements',
                'volatilite_signal_relatif',
                'volatilite_signal_absolu',
                'volatilite_groupe_comparaison',
                'volatilite_calculee_at',
                'volatilite_flag_manuel',
            ]);
        });
    }
};

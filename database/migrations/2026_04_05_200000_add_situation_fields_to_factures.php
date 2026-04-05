<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->unsignedTinyInteger('numero_situation')->nullable()->after('bon_commande_id');
            $table->decimal('pourcentage_avancement', 5, 2)->nullable()->after('numero_situation');
            $table->decimal('pourcentage_cumule', 5, 2)->nullable()->after('pourcentage_avancement');
            $table->decimal('montant_anterieur', 12, 4)->default(0)->after('pourcentage_cumule');
        });
    }

    public function down(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropColumn([
                'numero_situation', 'pourcentage_avancement',
                'pourcentage_cumule', 'montant_anterieur',
            ]);
        });
    }
};

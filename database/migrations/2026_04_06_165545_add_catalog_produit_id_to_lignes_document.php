<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lignes_document', function (Blueprint $table) {
            $table->foreignId('catalog_produit_id')
                ->nullable()
                ->after('produit_id')
                ->constrained('catalog_produits')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lignes_document', function (Blueprint $table) {
            $table->dropForeign(['catalog_produit_id']);
            $table->dropColumn('catalog_produit_id');
        });
    }
};

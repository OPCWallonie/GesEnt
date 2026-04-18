<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalog_produits', function (Blueprint $table) {
            $table->index('ean', 'idx_catalog_produits_ean');
        });
    }

    public function down(): void
    {
        Schema::table('catalog_produits', function (Blueprint $table) {
            $table->dropIndex('idx_catalog_produits_ean');
        });
    }
};

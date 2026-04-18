<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lignes_document', function (Blueprint $table) {
            $table->index(['catalog_produit_id', 'created_at']);
            $table->index(['produit_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('lignes_document', function (Blueprint $table) {
            $table->dropIndex(['catalog_produit_id', 'created_at']);
            $table->dropIndex(['produit_id', 'created_at']);
        });
    }
};

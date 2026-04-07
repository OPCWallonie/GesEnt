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
        Schema::create('produit_usage_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produit_id')->nullable()->constrained('produits')->cascadeOnDelete();
            $table->foreignId('catalog_produit_id')->nullable()->constrained('catalog_produits')->cascadeOnDelete();
            $table->unsignedInteger('nb_utilisations')->default(0);
            $table->unsignedInteger('nb_devis')->default(0);
            $table->date('derniere_utilisation')->nullable();
            $table->decimal('score', 8, 2)->default(0);
            $table->timestamps();

            $table->unique('produit_id');
            $table->unique('catalog_produit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produit_usage_stats');
    }
};

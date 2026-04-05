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
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->nullable()->unique();
            $table->string('designation', 255);
            $table->text('description')->nullable();
            $table->string('unite', 20)->default('pièce');  // pièce, m², ml, h, kg, etc.
            $table->decimal('prix_unitaire', 10, 4)->default(0);
            $table->decimal('taux_tva', 5, 2)->default(21.00);
            $table->string('categorie', 100)->nullable();
            $table->string('fournisseur', 100)->nullable();
            $table->string('reference_fournisseur', 50)->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};

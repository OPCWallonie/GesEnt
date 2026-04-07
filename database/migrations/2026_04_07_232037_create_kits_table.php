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
        Schema::create('kits', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 150);
            $table->text('description')->nullable();
            $table->string('categorie', 80)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('nb_utilisations')->default(0);
            $table->timestamps();
        });

        Schema::create('kit_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kit_id')->constrained('kits')->cascadeOnDelete();
            $table->integer('ordre')->default(0);
            $table->boolean('est_section')->default(false);
            $table->foreignId('produit_id')->nullable()->constrained('produits')->nullOnDelete();
            $table->foreignId('catalog_produit_id')->nullable()->constrained('catalog_produits')->nullOnDelete();
            $table->string('designation', 255);
            $table->text('detail')->nullable();
            $table->string('unite', 20)->default('pièce');
            $table->decimal('quantite', 10, 4)->default(1);
            $table->decimal('prix_unitaire', 12, 4)->default(0);
            $table->decimal('remise_valeur', 10, 4)->default(0);
            $table->enum('remise_type', ['montant', 'pourcentage'])->default('montant');
            $table->decimal('taux_tva', 5, 2)->default(21.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kit_lignes');
        Schema::dropIfExists('kits');
    }
};

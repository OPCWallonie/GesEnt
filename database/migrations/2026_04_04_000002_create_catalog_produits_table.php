<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_produits', function (Blueprint $table) {
            $table->id();
            $table->string('fournisseur', 50)->index();          // 'desco', 'vanmarke', 'autre'
            $table->string('reference', 100)->index();
            $table->text('designation');
            $table->text('description')->nullable();
            $table->string('unite', 20)->default('pièce');
            $table->decimal('prix_catalogue', 12, 4)->default(0); // prix d'achat HT fournisseur
            $table->decimal('prix_revente', 12, 4)->default(0);   // prix vente HT suggéré (avec marge)
            $table->decimal('taux_tva', 5, 2)->default(21.00);
            $table->string('categorie', 200)->nullable()->index();
            $table->string('sous_categorie', 200)->nullable();
            $table->string('marque', 100)->nullable();
            $table->string('ean', 30)->nullable();
            $table->boolean('en_stock')->default(true);
            $table->integer('quantite_stock')->nullable();
            $table->string('delai_livraison', 50)->nullable();
            $table->json('donnees_brutes')->nullable();
            $table->timestamp('derniere_sync')->nullable();
            $table->timestamps();

            $table->unique(['fournisseur', 'reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_produits');
    }
};

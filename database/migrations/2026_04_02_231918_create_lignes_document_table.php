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
        Schema::create('lignes_document', function (Blueprint $table) {
            $table->id();

            // Relation polymorphe : appartient à un devis, BDC, avenant ou facture
            $table->string('documentable_type');   // App\Models\Devis, BonCommande, etc.
            $table->unsignedBigInteger('documentable_id');
            $table->index(['documentable_type', 'documentable_id']);

            // Produit lié (optionnel — peut être saisi librement)
            $table->foreignId('produit_id')->nullable()->constrained('produits')->nullOnDelete();

            $table->integer('ordre')->default(0);               // Position dans le document
            $table->boolean('est_section')->default(false);     // Ligne titre/séparateur

            $table->string('designation', 255);
            $table->text('detail')->nullable();
            $table->string('unite', 20)->default('pièce');
            $table->decimal('quantite', 10, 4)->default(1);
            $table->decimal('prix_unitaire', 12, 4)->default(0);

            // Remise
            $table->decimal('remise_valeur', 10, 4)->default(0);
            $table->enum('remise_type', ['montant', 'pourcentage'])->default('montant');

            $table->decimal('taux_tva', 5, 2)->default(21.00);
            $table->decimal('montant_ht', 12, 4)->default(0);  // Calculé: (pu * qte) - remise

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lignes_document');
    }
};

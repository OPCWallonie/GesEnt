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
        Schema::create('devis', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();              // DEV/2026/0001
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->foreignId('chantier_id')->nullable()->constrained('chantiers')->nullOnDelete();
            $table->foreignId('mode_paiement_id')->nullable()->constrained('modes_paiement')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('statut', ['brouillon', 'en_attente', 'valide', 'refuse', 'expire', 'archive'])
                  ->default('brouillon');

            $table->date('date_document');
            $table->date('date_validite')->nullable();

            // Financier
            $table->decimal('montant_ht', 12, 4)->default(0);
            $table->decimal('montant_tva', 12, 4)->default(0);
            $table->decimal('montant_ttc', 12, 4)->default(0);
            $table->decimal('frais_port', 10, 4)->default(0);
            $table->decimal('ristourne_globale', 5, 2)->default(0);  // %
            $table->decimal('acompte', 10, 4)->default(0);
            $table->integer('delai_reglement')->default(30);          // jours

            // Notes
            $table->text('notes')->nullable();

            // Suivi conversion
            $table->date('date_statut')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devis');
    }
};

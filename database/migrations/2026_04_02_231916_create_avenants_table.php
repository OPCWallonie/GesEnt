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
        Schema::create('avenants', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 25)->unique();              // BDC/2026/0001/1
            $table->foreignId('bon_commande_id')->constrained('bons_commande')->cascadeOnDelete();
            $table->integer('numero_ordre');                     // 1, 2, 3...
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('statut', ['en_attente', 'valide', 'archive'])->default('en_attente');

            $table->date('date_document');
            $table->string('objet', 255)->nullable();           // Titre de l'avenant

            // Financier (cumulé dans la facture)
            $table->decimal('montant_ht', 12, 4)->default(0);
            $table->decimal('montant_tva', 12, 4)->default(0);
            $table->decimal('montant_ttc', 12, 4)->default(0);
            $table->decimal('frais_port', 10, 4)->default(0);
            $table->decimal('acompte', 10, 4)->default(0);

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['bon_commande_id', 'numero_ordre']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avenants');
    }
};

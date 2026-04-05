<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avoirs', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->foreignId('facture_id')->constrained('factures');
            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('chantier_id')->nullable()->constrained('chantiers');
            $table->foreignId('created_by')->constrained('users');
            $table->date('date_document');
            $table->text('motif');
            $table->decimal('montant_ht', 12, 4);
            $table->decimal('taux_tva', 5, 2)->default(21.00);
            $table->decimal('montant_tva', 12, 4);
            $table->decimal('montant_ttc', 12, 4);
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avoirs');
    }
};

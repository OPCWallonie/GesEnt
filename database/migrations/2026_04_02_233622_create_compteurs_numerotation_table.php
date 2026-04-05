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
        Schema::create('compteurs_numerotation', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->unique(); // 'devis', 'bon_commande', 'facture'
            $table->year('annee');
            $table->unsignedInteger('compteur')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compteurs_numerotation');
    }
};

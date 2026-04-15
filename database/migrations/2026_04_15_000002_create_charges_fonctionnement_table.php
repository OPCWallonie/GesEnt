<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('charges_fonctionnement', function (Blueprint $table) {
            $table->id();
            $table->string('libelle', 150);
            $table->string('categorie', 50);
            $table->decimal('montant_mensuel', 10, 2);
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->string('periodicite', 20)->default('mensuel');
            $table->text('notes')->nullable();
            $table->boolean('actif')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('charges_fonctionnement');
    }
};

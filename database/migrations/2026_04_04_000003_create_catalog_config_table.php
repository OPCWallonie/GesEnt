<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_config', function (Blueprint $table) {
            $table->id();
            $table->string('fournisseur', 50)->unique();
            $table->string('nom_affichage', 100);
            $table->boolean('actif')->default(true);
            $table->string('url_api')->nullable();
            $table->string('identifiant')->nullable();
            $table->text('mot_de_passe')->nullable();      // stocké encrypté
            $table->string('numero_client')->nullable();   // n° client chez le fournisseur
            $table->decimal('marge_defaut', 5, 2)->default(0); // % de marge appliqué au prix catalogue
            $table->timestamp('derniere_sync')->nullable();
            $table->integer('nb_produits')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_config');
    }
};

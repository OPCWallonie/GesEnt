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
        Schema::create('parametres_entreprise', function (Blueprint $table) {
            $table->id();
            // Identité
            $table->string('nom', 100)->default('');
            $table->string('statut_juridique', 20)->nullable();
            $table->string('adresse', 150)->nullable();
            $table->string('code_postal', 10)->nullable();
            $table->string('ville', 80)->nullable();
            $table->string('pays', 60)->default('Belgique');
            $table->string('telephone', 30)->nullable();
            $table->string('email', 120)->nullable();
            $table->string('site_web', 100)->nullable();
            $table->string('numero_tva', 30)->nullable();
            $table->string('numero_entreprise', 30)->nullable(); // BCE
            // Financier
            $table->string('iban', 34)->nullable();
            $table->string('bic', 11)->nullable();
            $table->string('banque', 80)->nullable();
            // PDF
            $table->string('logo_path', 255)->nullable();
            $table->text('conditions_generales')->nullable();
            $table->text('mentions_pied_page')->nullable();
            // Délais par défaut
            $table->integer('delai_reglement_defaut')->default(30);
            $table->integer('validite_devis_defaut')->default(30); // jours
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parametres_entreprise');
    }
};

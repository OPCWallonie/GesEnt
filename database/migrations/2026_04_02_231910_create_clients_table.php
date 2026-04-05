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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 100);
            $table->string('statut_juridique', 10)->nullable();   // SARL, SA, SRL, etc.
            $table->string('adresse', 150)->nullable();
            $table->string('code_postal', 10)->nullable();
            $table->string('ville', 80)->nullable();
            $table->string('pays', 60)->default('Belgique');
            $table->string('telephone', 30)->nullable();
            $table->string('fax', 20)->nullable();
            $table->string('gsm', 20)->nullable();
            $table->string('email', 120)->nullable();
            $table->string('site_web', 100)->nullable();
            $table->string('numero_tva', 30)->nullable()->unique();
            $table->string('numero_affiliation', 20)->nullable();
            $table->string('code_client', 20)->nullable()->unique();
            $table->text('notes')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};

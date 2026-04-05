<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fournisseurs', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('contact')->nullable();
            $table->string('email')->nullable();
            $table->string('telephone', 30)->nullable();
            $table->string('numero_tva', 30)->nullable();
            $table->string('numero_entreprise', 30)->nullable();
            $table->string('adresse', 150)->nullable();
            $table->string('code_postal', 10)->nullable();
            $table->string('ville', 80)->nullable();
            $table->string('pays', 60)->nullable()->default('Belgique');
            $table->text('notes')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fournisseurs');
    }
};

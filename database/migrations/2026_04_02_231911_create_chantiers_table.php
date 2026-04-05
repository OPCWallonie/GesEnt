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
        Schema::create('chantiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->string('nom', 150);
            $table->text('description')->nullable();
            $table->string('adresse_chantier', 150)->nullable();
            $table->string('code_postal', 10)->nullable();
            $table->string('ville', 80)->nullable();
            $table->string('pays', 60)->default('Belgique');
            $table->enum('statut', ['actif', 'inactif', 'termine', 'archive'])->default('actif');
            $table->date('date_debut')->nullable();
            $table->date('date_fin_prevue')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chantiers');
    }
};

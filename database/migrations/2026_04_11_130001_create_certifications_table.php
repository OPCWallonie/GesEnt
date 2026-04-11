<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ouvrier_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50);                         // clé dans Certification::TYPES
            $table->date('date_obtention');
            $table->date('date_expiration')->nullable();        // calculée auto ou saisie manuellement
            $table->string('organisme', 150)->nullable();       // organisme certificateur
            $table->string('numero_certificat', 100)->nullable();
            $table->string('document_path', 255)->nullable();   // scan du certificat
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['ouvrier_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certifications');
    }
};

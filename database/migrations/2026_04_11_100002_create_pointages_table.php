<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pointages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ouvrier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chantier_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('heures', 5, 2)->default(0);
            $table->decimal('heures_sup', 5, 2)->default(0);
            $table->decimal('cout_horaire', 8, 2)->default(0); // snapshot
            $table->decimal('cout_total', 10, 2)->default(0);  // auto-calculé
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['ouvrier_id', 'date', 'chantier_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pointages');
    }
};

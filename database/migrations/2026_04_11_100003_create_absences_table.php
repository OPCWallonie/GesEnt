<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ouvrier_id')->constrained()->cascadeOnDelete();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->string('type'); // conge, maladie, accident_travail, repos_compensatoire, autre
            $table->boolean('justifie')->default(true);
            $table->text('motif')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absences');
    }
};

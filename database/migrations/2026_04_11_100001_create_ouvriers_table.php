<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ouvriers', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom');
            $table->string('numero_national')->nullable()->unique();
            $table->string('categorie')->default('I'); // I, II, III, IV (CP124)
            $table->decimal('cout_horaire', 8, 2)->default(0);
            $table->date('date_entree');
            $table->date('date_sortie')->nullable();
            $table->boolean('actif')->default(true)->index();
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ouvriers');
    }
};

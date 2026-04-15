<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repos_collectifs', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->date('date');
            $table->boolean('demi_journee')->default(false);
            $table->string('perimetre', 30)->default('tous'); // 'tous', 'cp', 'type'
            $table->json('perimetre_valeurs')->nullable();    // CP codes ou type_personnel values
            $table->text('notes')->nullable();
            $table->boolean('applique')->default(false);
            $table->timestamp('applique_le')->nullable();
            $table->timestamps();

            $table->index('date');
            $table->index('applique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repos_collectifs');
    }
};

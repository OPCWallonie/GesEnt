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
        Schema::create('taux_tva', function (Blueprint $table) {
            $table->id();
            $table->decimal('taux', 5, 2)->unique();   // 0, 6, 12, 21
            $table->string('libelle', 50);             // "Exonéré", "Taux réduit", "Standard"
            $table->boolean('defaut')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taux_tva');
    }
};

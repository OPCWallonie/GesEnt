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
        Schema::create('produit_associations', function (Blueprint $table) {
            $table->id();
            $table->string('produit_a', 20);
            $table->string('produit_b', 20);
            $table->unsignedInteger('nb_cooccurrences')->default(0);
            $table->decimal('score', 8, 2)->default(0);
            $table->timestamps();

            $table->unique(['produit_a', 'produit_b']);
            $table->index('produit_a');
            $table->index('produit_b');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produit_associations');
    }
};

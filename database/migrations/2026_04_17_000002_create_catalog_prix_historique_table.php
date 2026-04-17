<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_prix_historique', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_produit_id')
                  ->constrained('catalog_produits')
                  ->cascadeOnDelete();
            $table->string('fournisseur', 50)->index();
            $table->string('reference', 100);
            $table->decimal('prix_avant', 12, 4);
            $table->decimal('prix_apres', 12, 4);
            $table->decimal('variation_pct', 6, 2);
            $table->boolean('est_significatif')->default(false);
            $table->enum('source', ['csv', 'api']);
            $table->timestamp('detected_at')->useCurrent();
            $table->timestamps();

            $table->index(['fournisseur', 'detected_at']);
            $table->index(['est_significatif', 'detected_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_prix_historique');
    }
};

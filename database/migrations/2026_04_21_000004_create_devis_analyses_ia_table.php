<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('devis_analyses_ia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('devis_id')
                  ->constrained('devis')
                  ->cascadeOnDelete();
            $table->string('hash_lignes', 64);
            $table->string('hash_alternatives', 64);
            $table->string('provider', 20);
            $table->string('modele', 100);
            $table->json('payload_envoye');
            $table->json('reponse_brute');
            $table->json('analyse');
            $table->unsignedInteger('duree_ms')->nullable();
            $table->unsignedInteger('cout_tokens_entree')->nullable();
            $table->unsignedInteger('cout_tokens_sortie')->nullable();
            $table->timestamp('genere_at');
            $table->timestamps();

            $table->unique('devis_id', 'uk_devis_analyses_ia_devis');
            $table->index('genere_at', 'idx_devis_analyses_ia_genere_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devis_analyses_ia');
    }
};

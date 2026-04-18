<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('document_type', ['devis', 'bon_commande', 'facture']);
            $table->unsignedBigInteger('document_id')->nullable();
            $table->json('data');
            $table->timestamp('saved_at');
            $table->timestamps();

            $table->index(['user_id', 'document_type', 'document_id'], 'idx_drafts_scope');
            $table->index('saved_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_drafts');
    }
};

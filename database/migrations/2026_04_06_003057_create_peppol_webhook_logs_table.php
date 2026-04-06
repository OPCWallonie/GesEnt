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
        Schema::create('peppol_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 30);
            $table->string('event_type', 50);
            $table->string('document_id', 100)->nullable();
            $table->enum('status', ['received', 'processed', 'failed', 'duplicate'])->default('received');
            $table->text('error_message')->nullable();
            $table->json('payload')->nullable();
            $table->foreignId('facture_achat_id')->nullable()->constrained('factures_achat')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peppol_webhook_logs');
    }
};

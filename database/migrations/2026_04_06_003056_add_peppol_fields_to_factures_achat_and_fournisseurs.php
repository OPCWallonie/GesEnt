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
        Schema::table('factures_achat', function (Blueprint $table) {
            $table->string('peppol_id', 100)->nullable()->after('notes');
            $table->string('peppol_sender_id', 50)->nullable()->after('peppol_id');
            $table->timestamp('peppol_recu_at')->nullable()->after('peppol_sender_id');
            $table->enum('peppol_source', ['manuel', 'ocr', 'peppol'])->default('manuel')->after('peppol_recu_at');
            $table->json('peppol_raw_data')->nullable()->after('peppol_source');
        });

        Schema::table('fournisseurs', function (Blueprint $table) {
            $table->string('peppol_id', 50)->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('factures_achat', function (Blueprint $table) {
            $table->dropColumn(['peppol_id', 'peppol_sender_id', 'peppol_recu_at', 'peppol_source', 'peppol_raw_data']);
        });

        Schema::table('fournisseurs', function (Blueprint $table) {
            $table->dropColumn('peppol_id');
        });
    }
};

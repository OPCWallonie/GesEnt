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
        Schema::table('avoirs', function (Blueprint $table) {
            $table->string('peppol_reference', 100)->nullable()->after('notes');
            $table->timestamp('peppol_envoye_at')->nullable()->after('peppol_reference');
        });
    }

    public function down(): void
    {
        Schema::table('avoirs', function (Blueprint $table) {
            $table->dropColumn(['peppol_reference', 'peppol_envoye_at']);
        });
    }
};

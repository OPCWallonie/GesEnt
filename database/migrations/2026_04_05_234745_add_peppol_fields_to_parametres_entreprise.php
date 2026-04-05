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
        Schema::table('parametres_entreprise', function (Blueprint $table) {
            $table->string('peppol_mode', 20)->default('desactive')->after('ai_url');
            $table->string('peppol_provider', 30)->nullable()->after('peppol_mode');
            $table->text('peppol_api_key')->nullable()->after('peppol_provider');
            $table->string('peppol_entity_id', 100)->nullable()->after('peppol_api_key');
            $table->string('peppol_id', 50)->nullable()->after('peppol_entity_id');
            $table->string('peppol_environment', 10)->default('sandbox')->after('peppol_id');
        });
    }

    public function down(): void
    {
        Schema::table('parametres_entreprise', function (Blueprint $table) {
            $table->dropColumn([
                'peppol_mode', 'peppol_provider', 'peppol_api_key',
                'peppol_entity_id', 'peppol_id', 'peppol_environment',
            ]);
        });
    }
};

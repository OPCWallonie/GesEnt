<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('parametres_entreprise', function (Blueprint $table) {
            $table->string('ai_provider')->nullable()->after('mentions_pied_page');
            $table->text('ai_api_key')->nullable()->after('ai_provider');   // chiffré
            $table->string('ai_model')->nullable()->after('ai_api_key');
            $table->string('ai_url')->nullable()->after('ai_model');        // Ollama / custom
        });
    }
    public function down(): void
    {
        Schema::table('parametres_entreprise', function (Blueprint $table) {
            $table->dropColumn(['ai_provider', 'ai_api_key', 'ai_model', 'ai_url']);
        });
    }
};

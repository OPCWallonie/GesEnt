<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absences', function (Blueprint $table) {
            $table->foreignId('repos_collectif_id')
                  ->nullable()
                  ->constrained('repos_collectifs')
                  ->nullOnDelete()
                  ->after('ouvrier_id');

            $table->boolean('demi_journee')->default(false)->after('date_fin');

            $table->index('repos_collectif_id');
        });
    }

    public function down(): void
    {
        Schema::table('absences', function (Blueprint $table) {
            $table->dropForeign(['repos_collectif_id']);
            $table->dropIndex(['repos_collectif_id']);
            $table->dropColumn(['repos_collectif_id', 'demi_journee']);
        });
    }
};

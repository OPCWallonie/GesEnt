<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ouvriers', function (Blueprint $table) {
            $table->json('qualifications')->nullable()->after('notes');
            $table->string('metier')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('ouvriers', function (Blueprint $table) {
            $table->dropColumn(['qualifications', 'metier']);
        });
    }
};

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
        if (!Schema::hasColumn('avoirs', 'statut')) {
            Schema::table('avoirs', function (Blueprint $table) {
                $table->string('statut', 30)->default('emis')->after('motif');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('avoirs', 'statut')) {
            Schema::table('avoirs', function (Blueprint $table) {
                $table->dropColumn('statut');
            });
        }
    }
};

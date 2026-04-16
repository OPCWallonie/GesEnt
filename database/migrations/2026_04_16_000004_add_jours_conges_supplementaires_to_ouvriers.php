<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ouvriers', function (Blueprint $table) {
            $table->unsignedTinyInteger('jours_conges_supplementaires')
                  ->default(0)
                  ->after('mode_heures_sup_defaut');
        });
    }

    public function down(): void
    {
        Schema::table('ouvriers', function (Blueprint $table) {
            $table->dropColumn('jours_conges_supplementaires');
        });
    }
};

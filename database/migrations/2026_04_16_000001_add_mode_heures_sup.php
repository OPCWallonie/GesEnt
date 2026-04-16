<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pointages', function (Blueprint $table) {
            $table->enum('mode_heures_sup', ['payees', 'recuperees'])
                  ->default('payees')
                  ->after('heures_sup');
        });

        Schema::table('ouvriers', function (Blueprint $table) {
            $table->enum('mode_heures_sup_defaut', ['payees', 'recuperees'])
                  ->default('payees')
                  ->after('heures_semaine');
        });
    }

    public function down(): void
    {
        Schema::table('pointages', function (Blueprint $table) {
            $table->dropColumn('mode_heures_sup');
        });
        Schema::table('ouvriers', function (Blueprint $table) {
            $table->dropColumn('mode_heures_sup_defaut');
        });
    }
};

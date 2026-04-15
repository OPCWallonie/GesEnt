<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ouvriers', function (Blueprint $table) {
            $table->decimal('heures_semaine', 4, 1)->default(40)->after('cout_mensuel');
        });
    }

    public function down(): void
    {
        Schema::table('ouvriers', function (Blueprint $table) {
            $table->dropColumn('heures_semaine');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            // nb_relances et derniere_relance_at existent déjà (migration 2026_04_02_234002)
            if (!Schema::hasColumn('factures', 'prochaine_relance_at')) {
                $table->date('prochaine_relance_at')->nullable()->after('derniere_relance_at');
            }
            if (!Schema::hasColumn('factures', 'relance_auto')) {
                $table->boolean('relance_auto')->default(true)->after('prochaine_relance_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropColumn(['prochaine_relance_at', 'relance_auto']);
        });
    }
};

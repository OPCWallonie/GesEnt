<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->decimal('montant_total_paye', 12, 4)->default(0)->after('montant_paye');
        });

        // Migrer les données existantes
        DB::table('factures')
            ->where('montant_paye', '>', 0)
            ->update(['montant_total_paye' => DB::raw('montant_paye')]);
    }

    public function down(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropColumn('montant_total_paye');
        });
    }
};

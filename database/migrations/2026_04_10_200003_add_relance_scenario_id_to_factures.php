<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->foreignId('relance_scenario_id')
                ->nullable()
                ->after('relance_auto')
                ->constrained('relance_scenarios')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\RelanceScenario::class);
            $table->dropColumn('relance_scenario_id');
        });
    }
};

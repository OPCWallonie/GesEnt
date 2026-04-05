<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->decimal('retenue_garantie_pct', 5, 2)->default(0)->after('acompte_deduit');
            $table->decimal('retenue_garantie_montant', 12, 4)->default(0)->after('retenue_garantie_pct');
            $table->date('retenue_garantie_liberee_at')->nullable()->after('retenue_garantie_montant');
        });
    }

    public function down(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropColumn(['retenue_garantie_pct', 'retenue_garantie_montant', 'retenue_garantie_liberee_at']);
        });
    }
};

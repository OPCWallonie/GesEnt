<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->unsignedTinyInteger('nb_relances')->default(0)->after('montant_paye');
            $table->date('derniere_relance_at')->nullable()->after('nb_relances');
        });
    }

    public function down(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropColumn(['nb_relances', 'derniere_relance_at']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parametres_entreprise', function (Blueprint $table) {
            $table->string('opc', 100)->nullable()->after('mail_template_relance');
            $table->string('opc_numero_affiliation', 50)->nullable()->after('opc');
        });
    }

    public function down(): void
    {
        Schema::table('parametres_entreprise', function (Blueprint $table) {
            $table->dropColumn(['opc', 'opc_numero_affiliation']);
        });
    }
};

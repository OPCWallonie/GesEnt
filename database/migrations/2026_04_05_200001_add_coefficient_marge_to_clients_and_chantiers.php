<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Pourcentage de marge à ajouter au prix catalogue (ex: 25 = +25%)
            $table->decimal('coefficient_marge', 5, 2)->nullable()->after('notes');
        });

        Schema::table('chantiers', function (Blueprint $table) {
            // Si renseigné, prime sur le coefficient du client
            $table->decimal('coefficient_marge', 5, 2)->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('coefficient_marge');
        });
        Schema::table('chantiers', function (Blueprint $table) {
            $table->dropColumn('coefficient_marge');
        });
    }
};

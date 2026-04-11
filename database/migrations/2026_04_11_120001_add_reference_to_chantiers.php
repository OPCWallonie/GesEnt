<?php

use App\Models\Chantier;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chantiers', function (Blueprint $table) {
            $table->string('reference', 20)->nullable()->unique()->after('nom');
        });

        // Générer les références pour les chantiers existants
        Chantier::whereNull('reference')->orderBy('id')->each(function (Chantier $chantier) {
            $chantier->reference = Chantier::genererReference($chantier);
            $chantier->saveQuietly(); // pas de re-déclenchement du booted
        });
    }

    public function down(): void
    {
        Schema::table('chantiers', function (Blueprint $table) {
            $table->dropUnique(['reference']);
            $table->dropColumn('reference');
        });
    }
};

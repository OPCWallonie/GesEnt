<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('chantiers', function (Blueprint $table) {
            $table->unsignedTinyInteger('avancement')->default(0)->after('statut');
            $table->date('date_debut_reel')->nullable()->after('avancement');
            $table->date('date_fin_reelle')->nullable()->after('date_debut_reel');
        });
    }
    public function down(): void
    {
        Schema::table('chantiers', function (Blueprint $table) {
            $table->dropColumn(['avancement', 'date_debut_reel', 'date_fin_reelle']);
        });
    }
};

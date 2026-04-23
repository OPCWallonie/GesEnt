<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('avenants', function (Blueprint $table) {
            $table->string('statut', 20)->default('en_attente')->change();
        });
    }

    public function down(): void
    {
        Schema::table('avenants', function (Blueprint $table) {
            $table->enum('statut', ['en_attente', 'valide', 'archive'])->default('en_attente')->change();
        });
    }
};

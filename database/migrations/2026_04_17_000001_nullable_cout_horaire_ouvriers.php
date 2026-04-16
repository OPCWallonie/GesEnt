<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ouvriers', function (Blueprint $table) {
            $table->decimal('cout_horaire', 8, 2)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('ouvriers', function (Blueprint $table) {
            // Revert to NOT NULL with 0 default (original state)
            $table->decimal('cout_horaire', 8, 2)->nullable(false)->default(0)->change();
        });
    }
};

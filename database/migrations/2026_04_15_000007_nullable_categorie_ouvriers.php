<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ouvriers', function (Blueprint $table) {
            $table->string('categorie')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('ouvriers', function (Blueprint $table) {
            $table->string('categorie')->nullable(false)->default('I')->change();
        });
    }
};

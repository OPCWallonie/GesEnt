<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->index('email');
            $table->index('code_client');
        });

        Schema::table('chantiers', function (Blueprint $table) {
            $table->index('ville');
        });

        Schema::table('lignes_document', function (Blueprint $table) {
            $table->index('designation');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['code_client']);
        });
        Schema::table('chantiers', function (Blueprint $table) {
            $table->dropIndex(['ville']);
        });
        Schema::table('lignes_document', function (Blueprint $table) {
            $table->dropIndex(['designation']);
        });
    }
};

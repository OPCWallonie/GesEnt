<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('parametres_entreprise', function (Blueprint $table) {
            $table->boolean('odoo_actif')->default(false)->after('peppol_webhook_token');
            $table->string('odoo_url', 255)->nullable()->after('odoo_actif');
            $table->string('odoo_database', 100)->nullable()->after('odoo_url');
            $table->string('odoo_username', 100)->nullable()->after('odoo_database');
            $table->text('odoo_api_key')->nullable()->after('odoo_username');
            $table->json('odoo_mapping')->nullable()->after('odoo_api_key');
            $table->enum('peppol_gere_par', ['gesent', 'odoo'])->default('gesent')->after('odoo_mapping');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->unsignedBigInteger('odoo_partner_id')->nullable()->after('coefficient_marge');
            $table->timestamp('odoo_synced_at')->nullable()->after('odoo_partner_id');
        });

        Schema::table('fournisseurs', function (Blueprint $table) {
            $table->unsignedBigInteger('odoo_partner_id')->nullable()->after('peppol_id');
            $table->timestamp('odoo_synced_at')->nullable()->after('odoo_partner_id');
        });

        Schema::table('factures', function (Blueprint $table) {
            $table->unsignedBigInteger('odoo_move_id')->nullable()->after('peppol_envoye_at');
            $table->timestamp('odoo_synced_at')->nullable()->after('odoo_move_id');
        });

        Schema::table('avoirs', function (Blueprint $table) {
            $table->unsignedBigInteger('odoo_move_id')->nullable()->after('peppol_envoye_at');
            $table->timestamp('odoo_synced_at')->nullable()->after('odoo_move_id');
        });

        Schema::table('factures_achat', function (Blueprint $table) {
            $table->unsignedBigInteger('odoo_move_id')->nullable()->after('peppol_raw_data');
            $table->timestamp('odoo_synced_at')->nullable()->after('odoo_move_id');
        });
    }

    public function down(): void
    {
        Schema::table('factures_achat', fn($t) => $t->dropColumn(['odoo_move_id', 'odoo_synced_at']));
        Schema::table('avoirs', fn($t) => $t->dropColumn(['odoo_move_id', 'odoo_synced_at']));
        Schema::table('factures', fn($t) => $t->dropColumn(['odoo_move_id', 'odoo_synced_at']));
        Schema::table('fournisseurs', fn($t) => $t->dropColumn(['odoo_partner_id', 'odoo_synced_at']));
        Schema::table('clients', fn($t) => $t->dropColumn(['odoo_partner_id', 'odoo_synced_at']));
        Schema::table('parametres_entreprise', fn($t) => $t->dropColumn([
            'odoo_actif', 'odoo_url', 'odoo_database', 'odoo_username',
            'odoo_api_key', 'odoo_mapping', 'peppol_gere_par',
        ]));
    }
};

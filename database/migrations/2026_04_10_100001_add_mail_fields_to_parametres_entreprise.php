<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parametres_entreprise', function (Blueprint $table) {
            $table->string('mail_host')->nullable()->after('deux_facteurs_obligatoires');
            $table->integer('mail_port')->nullable()->after('mail_host');
            $table->string('mail_encryption')->nullable()->after('mail_port');
            $table->string('mail_username')->nullable()->after('mail_encryption');
            $table->text('mail_password')->nullable()->after('mail_username');
            $table->string('mail_from_address')->nullable()->after('mail_password');
            $table->string('mail_from_name')->nullable()->after('mail_from_address');
            $table->text('mail_signature')->nullable()->after('mail_from_name');
            $table->text('mail_template_devis')->nullable()->after('mail_signature');
            $table->text('mail_template_facture')->nullable()->after('mail_template_devis');
            $table->text('mail_template_bdc')->nullable()->after('mail_template_facture');
            $table->text('mail_template_relance')->nullable()->after('mail_template_bdc');
        });
    }

    public function down(): void
    {
        Schema::table('parametres_entreprise', function (Blueprint $table) {
            $table->dropColumn([
                'mail_host', 'mail_port', 'mail_encryption', 'mail_username', 'mail_password',
                'mail_from_address', 'mail_from_name', 'mail_signature',
                'mail_template_devis', 'mail_template_facture', 'mail_template_bdc', 'mail_template_relance',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_envois', function (Blueprint $table) {
            $table->id();
            $table->morphs('document');
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('destinataire');
            $table->string('sujet');
            $table->text('message')->nullable();
            $table->string('statut', 20)->default('envoye'); // envoye | erreur
            $table->text('erreur')->nullable();
            $table->timestamp('envoye_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_envois');
    }
};

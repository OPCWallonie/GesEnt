<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('relance_etapes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relance_scenario_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('numero_ordre');
            $table->unsignedSmallInteger('delai_jours'); // jours après date_echeance
            $table->string('sujet');
            $table->text('corps_email');
            $table->string('canal')->default('mail'); // mail | courrier | les_deux
            $table->string('ton')->default('cordial'); // cordial | ferme | formel
            $table->boolean('actif')->default(true);
            $table->timestamps();

            $table->unique(['relance_scenario_id', 'numero_ordre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relance_etapes');
    }
};

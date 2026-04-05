<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('journal_chantier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chantier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['note', 'photo', 'jalon', 'probleme', 'reunion', 'livraison'])->default('note');
            $table->string('titre')->nullable();
            $table->text('contenu')->nullable();
            $table->json('photos')->nullable();
            $table->unsignedTinyInteger('avancement_apres')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('journal_chantier'); }
};

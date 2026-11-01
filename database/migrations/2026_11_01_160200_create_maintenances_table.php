<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('infrastructure_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technicien_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('titre');
            $table->text('notes')->nullable();
            $table->string('statut')->default('Planifiée');
            $table->date('date_prevue');
            $table->date('date_realisee')->nullable();
            $table->decimal('cout', 10, 2)->nullable();
            $table->timestamp('rappel_envoye_le')->nullable();
            $table->timestamps();

            $table->index(['statut', 'date_prevue']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};

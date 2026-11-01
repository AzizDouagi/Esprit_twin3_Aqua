<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mesures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('infrastructure_id')->constrained()->cascadeOnDelete();
            $table->decimal('debit', 8, 2)->nullable()->comment('m3/h');
            $table->decimal('pression', 6, 2)->nullable()->comment('bar');
            $table->decimal('niveau', 6, 2)->nullable()->comment('% de remplissage');
            $table->decimal('turbidite', 6, 2)->nullable()->comment('NTU');
            $table->decimal('ph', 4, 2)->nullable();
            $table->decimal('temperature', 5, 2)->nullable()->comment('°C');
            $table->boolean('anomalie')->default(false);
            $table->dateTime('mesure_le');
            $table->timestamps();

            $table->index(['infrastructure_id', 'mesure_le']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mesures');
    }
};

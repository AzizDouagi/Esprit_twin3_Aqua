<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anomaly_suggestions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mesure_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('incident_id')->nullable()->constrained()->nullOnDelete();
            $table->json('reasons');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anomaly_suggestions');
    }
};

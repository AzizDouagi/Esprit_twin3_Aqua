<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->foreignId('declarant_id')->nullable()->after('infrastructure_id')->constrained('users')->nullOnDelete();
            $table->foreignId('technicien_id')->nullable()->after('declarant_id')->constrained('users')->nullOnDelete();
            $table->timestamp('resolu_le')->nullable()->after('technicien_id');
        });

        Schema::create('incident_commentaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('contenu');
            $table->timestamps();
        });

        Schema::create('incident_historiques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('champ');
            $table->string('ancienne_valeur')->nullable();
            $table->string('nouvelle_valeur')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('incident_pieces_jointes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('chemin');
            $table->string('nom_original');
            $table->string('mime')->nullable();
            $table->unsignedInteger('taille')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_pieces_jointes');
        Schema::dropIfExists('incident_historiques');
        Schema::dropIfExists('incident_commentaires');
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('declarant_id');
            $table->dropConstrainedForeignId('technicien_id');
            $table->dropColumn('resolu_le');
        });
    }
};

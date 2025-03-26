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
        Schema::create('records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transcription_id')->constrained()->onDelete('cascade');
            $table->text('content');
            $table->string('language')->default('es');
            $table->string('service_used');
            $table->json('service_response')->nullable();
            $table->boolean('is_edited')->default(false);
            $table->boolean('email_sent')->default(false);
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamps();
            
            // Aquí se añaden los índices
            $table->index('transcription_id');
            $table->index('language');
            $table->fullText('content'); // Para búsqueda de texto completo (requiere MySQL 5.7+ o MariaDB 10.0+)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('records');
    }
};

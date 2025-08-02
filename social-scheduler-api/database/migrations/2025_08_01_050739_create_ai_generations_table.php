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
        Schema::create('ai_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('post_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('type'); // 'content', 'hashtags', 'ideas'
            $table->text('prompt');
            $table->json('generated_content');
            $table->string('model')->nullable(); // 'gpt-4', 'claude-3', etc.
            $table->integer('tokens_used')->nullable();
            $table->decimal('cost', 8, 4)->nullable();
            $table->json('metadata')->nullable(); // tone, platform, etc.
            $table->timestamps();
            
            $table->index(['user_id', 'type']);
            $table->index(['post_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_generations');
    }
}; 
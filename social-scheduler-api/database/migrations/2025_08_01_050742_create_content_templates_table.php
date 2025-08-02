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
        Schema::create('content_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('content');
            $table->json('hashtags')->nullable();
            $table->string('platform')->nullable(); // Specific platform or null for all
            $table->string('category')->nullable();
            $table->json('metadata')->nullable(); // tone, tags, etc.
            $table->boolean('is_evergreen')->default(false);
            $table->integer('recycle_interval_days')->nullable(); // For auto-recycling
            $table->timestamp('last_used_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['user_id', 'category']);
            $table->index(['user_id', 'is_evergreen']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_templates');
    }
}; 
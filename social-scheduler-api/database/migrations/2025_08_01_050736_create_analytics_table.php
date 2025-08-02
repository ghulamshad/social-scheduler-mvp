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
        Schema::create('analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('platform'); // twitter, facebook, instagram, linkedin
            $table->string('platform_post_id'); // ID from the platform
            $table->integer('likes')->default(0);
            $table->integer('shares')->default(0);
            $table->integer('comments')->default(0);
            $table->integer('clicks')->default(0);
            $table->integer('impressions')->default(0);
            $table->integer('reach')->default(0);
            $table->decimal('engagement_rate', 5, 2)->default(0);
            $table->json('additional_metrics')->nullable(); // Platform-specific metrics
            $table->timestamp('last_updated')->nullable();
            $table->timestamps();
            
            $table->unique(['post_id', 'platform']);
            $table->index(['platform', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics');
    }
}; 
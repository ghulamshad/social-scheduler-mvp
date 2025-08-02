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
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('post_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('account_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('platform'); // twitter, facebook, instagram, linkedin
            $table->string('event_type'); // post_created, post_updated, post_deleted, etc.
            $table->string('webhook_id')->nullable(); // Platform's webhook ID
            $table->json('payload'); // Raw webhook payload
            $table->string('status')->default('received'); // received, processed, failed
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->index(['platform', 'event_type']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhooks');
    }
}; 
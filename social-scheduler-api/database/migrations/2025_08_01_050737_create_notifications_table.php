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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // success, error, warning, info
            $table->string('title');
            $table->text('message');
            $table->string('category')->default('general'); // post, account, system, api
            $table->json('data')->nullable(); // Additional data
            $table->timestamp('read_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('channel')->default('database'); // database, email, sms, push
            $table->string('status')->default('pending'); // pending, sent, failed, read
            $table->timestamps();
            
            $table->index(['user_id', 'read_at']);
            $table->index(['type', 'status']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
}; 
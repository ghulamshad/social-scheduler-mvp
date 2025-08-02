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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('platform'); // twitter, facebook, instagram, linkedin
            $table->string('username');
            $table->string('avatar_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('platform_data')->nullable(); // Store platform-specific data
            $table->timestamps();
            
            $table->index(['user_id', 'platform']);
            $table->unique(['user_id', 'platform', 'username']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};

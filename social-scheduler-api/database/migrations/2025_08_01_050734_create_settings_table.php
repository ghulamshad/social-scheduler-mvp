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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('key')->index();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, json
            $table->string('category')->default('general'); // general, notifications, api, theme
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
}; 
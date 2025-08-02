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
        Schema::create('post_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('comments')->nullable();
            $table->json('changes_requested')->nullable(); // Suggested edits
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->index(['post_id', 'status']);
            $table->index(['requested_by', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_approvals');
    }
}; 
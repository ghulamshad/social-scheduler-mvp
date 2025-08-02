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
        Schema::table('posts', function (Blueprint $table) {
            // AI and content features
            $table->json('hashtags')->nullable()->after('content');
            $table->string('tone')->nullable()->after('hashtags'); // professional, casual, friendly, etc.
            $table->json('ai_generation_data')->nullable()->after('tone'); // Store AI generation metadata
            
            // Approval workflow
            $table->enum('approval_status', ['draft', 'pending_approval', 'approved', 'rejected'])->default('draft')->after('status');
            $table->foreignId('approved_by')->nullable()->constrained('users')->after('approval_status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            
            // Content constraints and validation
            $table->json('platform_constraints')->nullable()->after('approved_at'); // Store platform-specific limits
            $table->boolean('passes_validation')->default(true)->after('platform_constraints');
            $table->json('validation_errors')->nullable()->after('passes_validation');
            
            // Analytics and performance
            $table->integer('character_count')->nullable()->after('validation_errors');
            $table->json('performance_metrics')->nullable()->after('character_count'); // Store engagement data
            
            // Content recycling
            $table->foreignId('template_id')->nullable()->constrained('content_templates')->after('performance_metrics');
            $table->boolean('is_recycled')->default(false)->after('template_id');
            $table->timestamp('recycled_at')->nullable()->after('is_recycled');
            
            // Team collaboration
            $table->foreignId('team_id')->nullable()->constrained()->after('recycled_at');
            
            // Add indexes for new fields
            $table->index(['user_id', 'approval_status']);
            $table->index(['team_id', 'status']);
            $table->index(['schedule_time', 'approval_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['template_id']);
            $table->dropForeign(['team_id']);
            
            $table->dropColumn([
                'hashtags', 'tone', 'ai_generation_data', 'approval_status', 
                'approved_by', 'approved_at', 'platform_constraints', 'passes_validation',
                'validation_errors', 'character_count', 'performance_metrics',
                'template_id', 'is_recycled', 'recycled_at', 'team_id'
            ]);
            
            $table->dropIndex(['user_id', 'approval_status']);
            $table->dropIndex(['team_id', 'status']);
            $table->dropIndex(['schedule_time', 'approval_status']);
        });
    }
}; 
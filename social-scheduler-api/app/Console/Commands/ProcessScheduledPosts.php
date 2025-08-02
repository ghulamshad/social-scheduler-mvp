<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessScheduledPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posts:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process scheduled posts that are due for publishing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing scheduled posts...');

        $duePosts = Post::dueForPublishing()->with(['user', 'account'])->get();

        if ($duePosts->isEmpty()) {
            $this->info('No posts due for publishing.');
            return;
        }

        $this->info("Found {$duePosts->count()} posts to process.");

        foreach ($duePosts as $post) {
            $this->processPost($post);
        }

        $this->info('Post processing completed.');
    }

    private function processPost(Post $post)
    {
        $this->line("Processing post ID: {$post->id}");

        try {
            // Simulate publishing to social media
            $this->simulatePublishing($post);

            // Mark as published
            $platform = $post->account ? $post->account->platform : 'unknown platform';
            $post->markAsPublished("Published to {$platform}");

            $this->info("✓ Post {$post->id} published successfully");

            Log::info("Post published", [
                'post_id' => $post->id,
                'user_id' => $post->user_id,
                'account_id' => $post->account_id,
                'platform' => $post->account ? $post->account->platform : null,
                'content' => substr($post->content, 0, 100) . '...',
            ]);

        } catch (\Exception $e) {
            $post->markAsFailed("Failed to publish: " . $e->getMessage());

            $this->error("✗ Post {$post->id} failed to publish: " . $e->getMessage());

            Log::error("Post publishing failed", [
                'post_id' => $post->id,
                'user_id' => $post->user_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function simulatePublishing(Post $post)
    {
        // Simulate API call delay
        usleep(100000); // 0.1 seconds

        // Simulate occasional failures (5% chance)
        if (rand(1, 100) <= 5) {
            throw new \Exception('Simulated API failure');
        }

        // Simulate different platform behaviors
        $platform = $post->account ? $post->account->platform : 'unknown';
        
        switch ($platform) {
            case 'twitter':
                if (strlen($post->content) > 280) {
                    throw new \Exception('Content exceeds Twitter character limit');
                }
                break;
            case 'instagram':
                if (empty($post->image_path)) {
                    throw new \Exception('Instagram requires an image');
                }
                break;
            case 'facebook':
                // Facebook is more lenient
                break;
            case 'linkedin':
                if (strlen($post->content) > 3000) {
                    throw new \Exception('Content exceeds LinkedIn character limit');
                }
                break;
        }
    }
}

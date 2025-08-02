<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo user
        $user = User::create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => Hash::make('password'),
        ]);

        // Create demo social media accounts
        $accounts = [
            [
                'name' => 'My Twitter',
                'platform' => 'twitter',
                'username' => 'demouser',
                'avatar_url' => 'https://picsum.photos/150/150?random=1',
                'is_active' => true,
            ],
            [
                'name' => 'Business Facebook',
                'platform' => 'facebook',
                'username' => 'demobusiness',
                'avatar_url' => 'https://picsum.photos/150/150?random=2',
                'is_active' => true,
            ],
            [
                'name' => 'Personal Instagram',
                'platform' => 'instagram',
                'username' => 'demo.photography',
                'avatar_url' => 'https://picsum.photos/150/150?random=3',
                'is_active' => true,
            ],
            [
                'name' => 'Professional LinkedIn',
                'platform' => 'linkedin',
                'username' => 'demo-professional',
                'avatar_url' => 'https://picsum.photos/150/150?random=4',
                'is_active' => true,
            ],
        ];

        foreach ($accounts as $accountData) {
            Account::create(array_merge($accountData, ['user_id' => $user->id]));
        }

        // Get the created accounts for post creation
        $twitterAccount = Account::where('platform', 'twitter')->first();
        $facebookAccount = Account::where('platform', 'facebook')->first();
        $instagramAccount = Account::where('platform', 'instagram')->first();
        $linkedinAccount = Account::where('platform', 'linkedin')->first();

        // Create demo posts with different statuses
        $posts = [
            [
                'content' => 'Just launched our new product! 🚀 Excited to share this with everyone. #ProductLaunch #Innovation',
                'schedule_time' => now()->addHours(2),
                'account_id' => $twitterAccount->id,
                'status' => 'scheduled',
                'image_path' => 'https://picsum.photos/600/400?random=5',
            ],
            [
                'content' => 'Great meeting with our team today! Collaboration is key to success. 💪',
                'schedule_time' => now()->addDays(1),
                'account_id' => $facebookAccount->id,
                'status' => 'scheduled',
            ],
            [
                'content' => 'Beautiful sunset captured today! Nature never fails to amaze. 🌅 #Photography #Nature',
                'schedule_time' => now()->addDays(2),
                'account_id' => $instagramAccount->id,
                'status' => 'scheduled',
                'image_path' => 'https://picsum.photos/600/600?random=6',
            ],
            [
                'content' => 'Excited to announce that I\'ve joined the board of directors at TechCorp! Looking forward to contributing to the company\'s growth and innovation. #Leadership #Technology #CareerGrowth',
                'schedule_time' => now()->addDays(3),
                'account_id' => $linkedinAccount->id,
                'status' => 'scheduled',
            ],
            [
                'content' => 'Our latest blog post is live! Check out "10 Tips for Better Productivity" - packed with actionable advice. Link in bio! 📝',
                'schedule_time' => now()->subHours(1),
                'account_id' => $twitterAccount->id,
                'status' => 'published',
                'published_at' => now()->subMinutes(30),
                'publish_log' => 'Successfully published to Twitter',
            ],
            [
                'content' => 'Happy Friday everyone! 🎉 What are your plans for the weekend?',
                'schedule_time' => now()->subDays(1),
                'account_id' => $facebookAccount->id,
                'status' => 'published',
                'published_at' => now()->subDays(1)->addMinutes(5),
                'publish_log' => 'Successfully published to Facebook',
            ],
            [
                'content' => 'This is a very long post that exceeds the Twitter character limit of 280 characters. It contains a lot of text that would normally cause the post to fail when trying to publish to Twitter because it\'s too long for the platform\'s requirements. This demonstrates how the system handles content validation and error logging.',
                'schedule_time' => now()->subHours(2),
                'account_id' => $twitterAccount->id,
                'status' => 'failed',
                'publish_log' => 'Failed to publish: Content exceeds Twitter character limit',
            ],
            [
                'content' => 'Amazing coffee art! ☕️',
                'schedule_time' => now()->subHours(3),
                'account_id' => $instagramAccount->id,
                'status' => 'failed',
                'publish_log' => 'Failed to publish: Instagram requires an image',
            ],
        ];

        foreach ($posts as $postData) {
            Post::create(array_merge($postData, ['user_id' => $user->id]));
        }

        $this->command->info('Demo data seeded successfully!');
        $this->command->info('Demo user: demo@example.com / password');
        $this->command->info('Created 4 social media accounts and 8 sample posts');
    }
}

<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Account;
use App\Exceptions\SocialMediaException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Abraham\TwitterOAuth\TwitterOAuth;
use Facebook\Facebook;

class SocialMediaService
{
    protected $rateLimiter;
    
    public function __construct()
    {
        $this->rateLimiter = app('cache');
    }
    
    /**
     * Publish post to social media platform
     */
    public function publish(Post $post): array
    {
        $platform = $post->account->platform ?? 'unknown';
        
        // Check rate limits
        $this->checkRateLimit($platform, $post->user_id);
        
        try {
            switch ($platform) {
                case 'twitter':
                    return $this->publishToTwitter($post);
                case 'facebook':
                    return $this->publishToFacebook($post);
                case 'instagram':
                    return $this->publishToInstagram($post);
                case 'linkedin':
                    return $this->publishToLinkedIn($post);
                default:
                    throw new SocialMediaException("Unsupported platform: {$platform}", $platform, $post->id);
            }
        } catch (SocialMediaException $e) {
            Log::error("Social media publishing failed", [
                'post_id' => $post->id,
                'platform' => $platform,
                'error' => $e->getMessage(),
                'retryable' => $e->isRetryable()
            ]);
            throw $e;
        }
    }
    
    /**
     * Publish to Twitter
     */
    protected function publishToTwitter(Post $post): array
    {
        $connection = new TwitterOAuth(
            env('TWITTER_CONSUMER_KEY'),
            env('TWITTER_CONSUMER_SECRET'),
            $post->account->access_token,
            $post->account->access_token_secret
        );
        
        // Validate content
        if (strlen($post->content) > 280) {
            throw new SocialMediaException('Content exceeds Twitter character limit (280)', 'twitter', $post->id, false);
        }
        
        $params = ['text' => $post->content];
        
        // Add media if available
        if ($post->image_path) {
            $media = $connection->upload('media/upload', ['media' => $post->image_path]);
            if (isset($media->media_id_string)) {
                $params['media'] = ['media_ids' => [$media->media_id_string]];
            }
        }
        
        $result = $connection->post('tweets', $params);
        
        if (isset($result->errors)) {
            throw new SocialMediaException(
                'Twitter API error: ' . $result->errors[0]->message,
                'twitter',
                $post->id
            );
        }
        
        return [
            'platform' => 'twitter',
            'post_id' => $result->data->id,
            'url' => "https://twitter.com/user/status/{$result->data->id}",
            'success' => true
        ];
    }
    
    /**
     * Publish to Facebook
     */
    protected function publishToFacebook(Post $post): array
    {
        $fb = new Facebook([
            'app_id' => env('FACEBOOK_APP_ID'),
            'app_secret' => env('FACEBOOK_APP_SECRET'),
            'default_graph_version' => 'v18.0',
        ]);
        
        $params = [
            'message' => $post->content,
            'access_token' => $post->account->access_token,
        ];
        
        // Add image if available
        if ($post->image_path) {
            $params['source'] = $fb->fileToUpload($post->image_path);
        }
        
        try {
            $response = $fb->post("/{$post->account->platform_id}/feed", $params);
            $result = $response->getGraphNode();
            
            return [
                'platform' => 'facebook',
                'post_id' => $result['id'],
                'url' => "https://facebook.com/{$result['id']}",
                'success' => true
            ];
        } catch (\Exception $e) {
            throw new SocialMediaException(
                'Facebook API error: ' . $e->getMessage(),
                'facebook',
                $post->id
            );
        }
    }
    
    /**
     * Publish to Instagram
     */
    protected function publishToInstagram(Post $post): array
    {
        if (empty($post->image_path)) {
            throw new SocialMediaException('Instagram requires an image', 'instagram', $post->id, false);
        }
        
        $fb = new Facebook([
            'app_id' => env('FACEBOOK_APP_ID'),
            'app_secret' => env('FACEBOOK_APP_SECRET'),
            'default_graph_version' => 'v18.0',
        ]);
        
        try {
            // First, upload the image
            $response = $fb->post("/{$post->account->platform_id}/media", [
                'image_url' => $post->image_path,
                'caption' => $post->content,
                'access_token' => $post->account->access_token,
            ]);
            
            $mediaId = $response->getGraphNode()['id'];
            
            // Then publish the container
            $response = $fb->post("/{$post->account->platform_id}/media_publish", [
                'creation_id' => $mediaId,
                'access_token' => $post->account->access_token,
            ]);
            
            $result = $response->getGraphNode();
            
            return [
                'platform' => 'instagram',
                'post_id' => $result['id'],
                'url' => "https://instagram.com/p/{$result['id']}",
                'success' => true
            ];
        } catch (\Exception $e) {
            throw new SocialMediaException(
                'Instagram API error: ' . $e->getMessage(),
                'instagram',
                $post->id
            );
        }
    }
    
    /**
     * Publish to LinkedIn
     */
    protected function publishToLinkedIn(Post $post): array
    {
        if (strlen($post->content) > 3000) {
            throw new SocialMediaException('Content exceeds LinkedIn character limit (3000)', 'linkedin', $post->id, false);
        }
        
        $client = new \GuzzleHttp\Client();
        
        try {
            $response = $client->post("https://api.linkedin.com/v2/ugcPosts", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $post->account->access_token,
                    'Content-Type' => 'application/json',
                    'X-Restli-Protocol-Version' => '2.0.0',
                ],
                'json' => [
                    'author' => "urn:li:person:{$post->account->platform_id}",
                    'lifecycleState' => 'PUBLISHED',
                    'specificContent' => [
                        'com.linkedin.ugc.ShareContent' => [
                            'shareCommentary' => [
                                'text' => $post->content
                            ],
                            'shareMediaCategory' => 'NONE'
                        ]
                    ],
                    'visibility' => [
                        'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC'
                    ]
                ]
            ]);
            
            $result = json_decode($response->getBody(), true);
            
            return [
                'platform' => 'linkedin',
                'post_id' => $result['id'],
                'url' => "https://linkedin.com/feed/update/{$result['id']}",
                'success' => true
            ];
        } catch (\Exception $e) {
            throw new SocialMediaException(
                'LinkedIn API error: ' . $e->getMessage(),
                'linkedin',
                $post->id
            );
        }
    }
    
    /**
     * Check rate limits for platform
     */
    protected function checkRateLimit(string $platform, int $userId): void
    {
        $key = "rate_limit_{$platform}_{$userId}";
        $limits = $this->getRateLimits($platform);
        
        if ($this->rateLimiter->has($key)) {
            $attempts = $this->rateLimiter->get($key);
            if ($attempts >= $limits['max_attempts']) {
                $retryAfter = $this->rateLimiter->get("{$key}_reset") - time();
                throw new SocialMediaException(
                    "Rate limit exceeded. Try again in {$retryAfter} seconds.",
                    $platform,
                    null,
                    true
                );
            }
        }
        
        $this->rateLimiter->put($key, 1, $limits['decay_minutes'] * 60);
        $this->rateLimiter->put("{$key}_reset", time() + ($limits['decay_minutes'] * 60), $limits['decay_minutes'] * 60);
    }
    
    /**
     * Get rate limits for platform
     */
    protected function getRateLimits(string $platform): array
    {
        return [
            'twitter' => ['max_attempts' => 300, 'decay_minutes' => 15],
            'facebook' => ['max_attempts' => 200, 'decay_minutes' => 60],
            'instagram' => ['max_attempts' => 100, 'decay_minutes' => 60],
            'linkedin' => ['max_attempts' => 100, 'decay_minutes' => 1440], // 24 hours
        ][$platform] ?? ['max_attempts' => 100, 'decay_minutes' => 60];
    }
    
    /**
     * Validate post for platform requirements
     */
    public function validatePost(Post $post): array
    {
        $platform = $post->account->platform ?? 'unknown';
        $errors = [];
        
        switch ($platform) {
            case 'twitter':
                if (strlen($post->content) > 280) {
                    $errors[] = 'Content exceeds Twitter character limit (280)';
                }
                break;
            case 'instagram':
                if (empty($post->image_path)) {
                    $errors[] = 'Instagram requires an image';
                }
                break;
            case 'linkedin':
                if (strlen($post->content) > 3000) {
                    $errors[] = 'Content exceeds LinkedIn character limit (3000)';
                }
                break;
        }
        
        return $errors;
    }
} 
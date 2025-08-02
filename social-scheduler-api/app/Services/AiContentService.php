<?php

namespace App\Services;

use App\Models\AiGeneration;
use App\Models\User;
use App\Models\Post;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AiContentService
{
    protected $openaiApiKey;
    protected $claudeApiKey;
    protected $defaultModel = 'gpt-4';

    public function __construct()
    {
        $this->openaiApiKey = config('services.openai.api_key');
        $this->claudeApiKey = config('services.claude.api_key');
    }

    /**
     * Generate post content using AI
     */
    public function generateContent(
        User $user,
        array $params = [],
        string $model = null
    ): array {
        $prompt = $this->buildContentPrompt($params);
        $model = $model ?? $this->defaultModel;

        try {
            $response = $this->callAiApi($prompt, $model);
            
            $generation = AiGeneration::create([
                'user_id' => $user->id,
                'type' => 'content',
                'prompt' => $prompt,
                'generated_content' => $response['content'],
                'model' => $model,
                'tokens_used' => $response['tokens_used'] ?? null,
                'cost' => $response['cost'] ?? null,
                'metadata' => $params,
            ]);

            return [
                'success' => true,
                'content' => $response['content']['text'] ?? '',
                'hashtags' => $response['content']['hashtags'] ?? [],
                'generation_id' => $generation->id,
            ];
        } catch (\Exception $e) {
            Log::error('AI content generation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'params' => $params,
            ]);

            return [
                'success' => false,
                'error' => 'Failed to generate content. Please try again.',
            ];
        }
    }

    /**
     * Generate hashtag recommendations
     */
    public function generateHashtags(
        User $user,
        string $content,
        string $platform,
        array $params = []
    ): array {
        $prompt = $this->buildHashtagPrompt($content, $platform, $params);
        $model = $params['model'] ?? $this->defaultModel;

        try {
            $response = $this->callAiApi($prompt, $model);
            
            $generation = AiGeneration::create([
                'user_id' => $user->id,
                'type' => 'hashtags',
                'prompt' => $prompt,
                'generated_content' => $response['content'],
                'model' => $model,
                'tokens_used' => $response['tokens_used'] ?? null,
                'cost' => $response['cost'] ?? null,
                'metadata' => array_merge($params, ['platform' => $platform]),
            ]);

            return [
                'success' => true,
                'hashtags' => $response['content']['hashtags'] ?? [],
                'generation_id' => $generation->id,
            ];
        } catch (\Exception $e) {
            Log::error('AI hashtag generation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to generate hashtags. Please try again.',
            ];
        }
    }

    /**
     * Generate content ideas
     */
    public function generateIdeas(
        User $user,
        array $params = []
    ): array {
        $prompt = $this->buildIdeasPrompt($params);
        $model = $params['model'] ?? $this->defaultModel;

        try {
            $response = $this->callAiApi($prompt, $model);
            
            $generation = AiGeneration::create([
                'user_id' => $user->id,
                'type' => 'ideas',
                'prompt' => $prompt,
                'generated_content' => $response['content'],
                'model' => $model,
                'tokens_used' => $response['tokens_used'] ?? null,
                'cost' => $response['cost'] ?? null,
                'metadata' => $params,
            ]);

            return [
                'success' => true,
                'ideas' => $response['content']['ideas'] ?? [],
                'generation_id' => $generation->id,
            ];
        } catch (\Exception $e) {
            Log::error('AI ideas generation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to generate ideas. Please try again.',
            ];
        }
    }

    /**
     * Build content generation prompt
     */
    protected function buildContentPrompt(array $params): string
    {
        $tone = $params['tone'] ?? 'professional';
        $platform = $params['platform'] ?? 'general';
        $topic = $params['topic'] ?? '';
        $industry = $params['industry'] ?? '';
        $targetAudience = $params['target_audience'] ?? '';

        return "Generate a social media post for {$platform} with the following requirements:
        
        Tone: {$tone}
        Topic: {$topic}
        Industry: {$industry}
        Target Audience: {$targetAudience}
        
        Please provide:
        1. Engaging post content (optimized for {$platform})
        2. 3-5 relevant hashtags
        3. Suggested posting time
        4. Call-to-action if appropriate
        
        Format the response as JSON with keys: text, hashtags, suggested_time, cta";
    }

    /**
     * Build hashtag generation prompt
     */
    protected function buildHashtagPrompt(string $content, string $platform, array $params): string
    {
        $industry = $params['industry'] ?? '';
        $tone = $params['tone'] ?? 'professional';

        return "Generate 5-8 relevant hashtags for this {$platform} post:
        
        Content: {$content}
        Industry: {$industry}
        Tone: {$tone}
        
        Consider:
        - Platform-specific hashtag trends
        - Industry relevance
        - Engagement potential
        - Mix of popular and niche hashtags
        
        Return as JSON array of hashtags.";
    }

    /**
     * Build ideas generation prompt
     */
    protected function buildIdeasPrompt(array $params): string
    {
        $industry = $params['industry'] ?? '';
        $platform = $params['platform'] ?? 'general';
        $tone = $params['tone'] ?? 'professional';
        $count = $params['count'] ?? 5;

        return "Generate {$count} engaging social media post ideas for {$platform}:
        
        Industry: {$industry}
        Tone: {$tone}
        
        Each idea should include:
        - Post topic/theme
        - Content angle
        - Suggested hashtags
        - Best posting time
        - Expected engagement
        
        Return as JSON array of ideas.";
    }

    /**
     * Call AI API (OpenAI or Claude)
     */
    protected function callAiApi(string $prompt, string $model): array
    {
        if (str_contains($model, 'gpt')) {
            return $this->callOpenAI($prompt, $model);
        } elseif (str_contains($model, 'claude')) {
            return $this->callClaude($prompt, $model);
        }

        throw new \Exception("Unsupported AI model: {$model}");
    }

    /**
     * Call OpenAI API
     */
    protected function callOpenAI(string $prompt, string $model): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->openaiApiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => 'You are a social media content expert. Always respond with valid JSON.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.7,
            'max_tokens' => 1000,
        ]);

        if (!$response->successful()) {
            throw new \Exception('OpenAI API error: ' . $response->body());
        }

        $data = $response->json();
        $content = json_decode($data['choices'][0]['message']['content'], true);

        return [
            'content' => $content,
            'tokens_used' => $data['usage']['total_tokens'] ?? null,
            'cost' => $this->calculateOpenAICost($data['usage']['total_tokens'] ?? 0, $model),
        ];
    }

    /**
     * Call Claude API
     */
    protected function callClaude(string $prompt, string $model): array
    {
        $response = Http::withHeaders([
            'x-api-key' => $this->claudeApiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => $model,
            'max_tokens' => 1000,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        if (!$response->successful()) {
            throw new \Exception('Claude API error: ' . $response->body());
        }

        $data = $response->json();
        $content = json_decode($data['content'][0]['text'], true);

        return [
            'content' => $content,
            'tokens_used' => $data['usage']['input_tokens'] + $data['usage']['output_tokens'] ?? null,
            'cost' => $this->calculateClaudeCost($data['usage']['input_tokens'] + $data['usage']['output_tokens'] ?? 0, $model),
        ];
    }

    /**
     * Calculate OpenAI cost
     */
    protected function calculateOpenAICost(int $tokens, string $model): float
    {
        $rates = [
            'gpt-4' => 0.03 / 1000,
            'gpt-4-turbo' => 0.01 / 1000,
            'gpt-3.5-turbo' => 0.002 / 1000,
        ];

        return ($rates[$model] ?? 0.01 / 1000) * $tokens;
    }

    /**
     * Calculate Claude cost
     */
    protected function calculateClaudeCost(int $tokens, string $model): float
    {
        $rates = [
            'claude-3-opus' => 0.015 / 1000,
            'claude-3-sonnet' => 0.003 / 1000,
            'claude-3-haiku' => 0.00025 / 1000,
        ];

        return ($rates[$model] ?? 0.003 / 1000) * $tokens;
    }

    /**
     * Get user's AI usage statistics
     */
    public function getUserStats(User $user): array
    {
        $generations = AiGeneration::where('user_id', $user->id)->get();

        return [
            'total_generations' => $generations->count(),
            'total_tokens' => $generations->sum('tokens_used'),
            'total_cost' => $generations->sum('cost'),
            'by_type' => $generations->groupBy('type')->map->count(),
            'by_model' => $generations->groupBy('model')->map->count(),
        ];
    }
} 
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AiContentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AiController extends Controller
{
    protected $aiService;

    public function __construct(AiContentService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Generate post content
     */
    public function generateContent(Request $request): JsonResponse
    {
        $request->validate([
            'topic' => 'required|string|max:255',
            'platform' => 'required|string|in:twitter,facebook,instagram,linkedin',
            'tone' => 'sometimes|string|in:professional,casual,friendly,humorous,formal',
            'industry' => 'sometimes|string|max:255',
            'target_audience' => 'sometimes|string|max:255',
            'model' => 'sometimes|string|in:gpt-4,gpt-4-turbo,gpt-3.5-turbo,claude-3-opus,claude-3-sonnet,claude-3-haiku',
        ]);

        $user = Auth::user();
        $params = $request->only(['topic', 'platform', 'tone', 'industry', 'target_audience']);
        $model = $request->get('model');

        $result = $this->aiService->generateContent($user, $params, $model);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['error']
            ], 400);
        }

        return response()->json([
            'message' => 'Content generated successfully',
            'data' => [
                'content' => $result['content'],
                'hashtags' => $result['hashtags'],
                'generation_id' => $result['generation_id'],
            ]
        ]);
    }

    /**
     * Generate hashtags for existing content
     */
    public function generateHashtags(Request $request): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:3000',
            'platform' => 'required|string|in:twitter,facebook,instagram,linkedin',
            'industry' => 'sometimes|string|max:255',
            'tone' => 'sometimes|string|in:professional,casual,friendly,humorous,formal',
            'model' => 'sometimes|string|in:gpt-4,gpt-4-turbo,gpt-3.5-turbo,claude-3-opus,claude-3-sonnet,claude-3-haiku',
        ]);

        $user = Auth::user();
        $params = $request->only(['industry', 'tone']);
        $model = $request->get('model');

        $result = $this->aiService->generateHashtags(
            $user,
            $request->content,
            $request->platform,
            $params
        );

        if (!$result['success']) {
            return response()->json([
                'message' => $result['error']
            ], 400);
        }

        return response()->json([
            'message' => 'Hashtags generated successfully',
            'data' => [
                'hashtags' => $result['hashtags'],
                'generation_id' => $result['generation_id'],
            ]
        ]);
    }

    /**
     * Generate content ideas
     */
    public function generateIdeas(Request $request): JsonResponse
    {
        $request->validate([
            'industry' => 'required|string|max:255',
            'platform' => 'required|string|in:twitter,facebook,instagram,linkedin',
            'tone' => 'sometimes|string|in:professional,casual,friendly,humorous,formal',
            'count' => 'sometimes|integer|min:1|max:10',
            'model' => 'sometimes|string|in:gpt-4,gpt-4-turbo,gpt-3.5-turbo,claude-3-opus,claude-3-sonnet,claude-3-haiku',
        ]);

        $user = Auth::user();
        $params = $request->only(['industry', 'platform', 'tone', 'count']);
        $model = $request->get('model');

        $result = $this->aiService->generateIdeas($user, $params);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['error']
            ], 400);
        }

        return response()->json([
            'message' => 'Ideas generated successfully',
            'data' => [
                'ideas' => $result['ideas'],
                'generation_id' => $result['generation_id'],
            ]
        ]);
    }

    /**
     * Get AI usage statistics
     */
    public function getStats(): JsonResponse
    {
        $user = Auth::user();
        $stats = $this->aiService->getUserStats($user);

        return response()->json([
            'data' => $stats
        ]);
    }

    /**
     * Get available AI models
     */
    public function getModels(): JsonResponse
    {
        $models = [
            'openai' => [
                'gpt-4' => 'GPT-4 (Most capable)',
                'gpt-4-turbo' => 'GPT-4 Turbo (Fast & capable)',
                'gpt-3.5-turbo' => 'GPT-3.5 Turbo (Fast & affordable)',
            ],
            'anthropic' => [
                'claude-3-opus' => 'Claude 3 Opus (Most capable)',
                'claude-3-sonnet' => 'Claude 3 Sonnet (Balanced)',
                'claude-3-haiku' => 'Claude 3 Haiku (Fast & affordable)',
            ]
        ];

        return response()->json([
            'data' => $models
        ]);
    }
} 
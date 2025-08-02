<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = $user->posts()->with(['account', 'team', 'approvedBy']);

        // Filter by team if specified
        if ($request->has('team_id')) {
            $team = Team::findOrFail($request->team_id);
            if ($team->hasMember($user->id)) {
                $query->where('team_id', $request->team_id);
            }
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by approval status
        if ($request->has('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        // Filter by platform
        if ($request->has('platform')) {
            $query->whereHas('account', function ($q) use ($request) {
                $q->where('platform', $request->platform);
            });
        }

        $posts = $query->orderBy('schedule_time', 'desc')->paginate(20);

        return response()->json($posts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:3000',
            'schedule_time' => 'required|date|after:now',
            'account_id' => 'nullable|exists:accounts,id',
            'image_path' => 'nullable|string',
            'hashtags' => 'nullable|array',
            'tone' => 'nullable|string|in:professional,casual,friendly,humorous,formal',
            'team_id' => 'nullable|exists:teams,id',
            'requires_approval' => 'nullable|boolean',
        ]);

        $user = $request->user();
        $scheduleTime = \Carbon\Carbon::parse($request->schedule_time);

        // Check team permissions if team_id is provided
        if ($request->team_id) {
            $team = Team::findOrFail($request->team_id);
            if (!$team->hasMember($user->id)) {
                return response()->json(['message' => 'Unauthorized to create posts for this team'], 403);
            }
        }

        $postData = [
            'content' => $request->content,
            'schedule_time' => $request->schedule_time,
            'account_id' => $request->account_id,
            'image_path' => $request->image_path,
            'hashtags' => $request->hashtags,
            'tone' => $request->tone,
            'team_id' => $request->team_id,
            'status' => 'scheduled',
        ];

        // Set approval status
        if ($request->requires_approval && $request->team_id) {
            $postData['approval_status'] = 'pending_approval';
        } else {
            $postData['approval_status'] = 'approved';
        }

        $post = $user->posts()->create($postData);

        // Validate content constraints
        $validationErrors = $post->validateContent();

        // Request approval if needed
        if ($request->requires_approval && $request->team_id) {
            $post->requestApproval($user->id);
        }

        // Send notification for post creation
        \App\Http\Controllers\Api\NotificationController::sendSuccess(
            $user->id,
            'Post Scheduled',
            "Your post has been scheduled for {$scheduleTime->format('M j, Y g:i A')}",
            'post',
            ['post_id' => $post->id, 'account_id' => $request->account_id]
        );

        return response()->json([
            'data' => $post->load(['account', 'team']),
            'validation_errors' => $validationErrors,
        ], Response::HTTP_CREATED);
    }

    public function show(Request $request, Post $post)
    {
        $user = $request->user();
        
        // Check if user owns the post or is team member
        if ($post->user_id !== $user->id && (!$post->team_id || !$post->team->hasMember($user->id))) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'data' => $post->load(['account', 'team', 'approvedBy', 'approvals.requestedBy', 'approvals.approvedBy'])
        ]);
    }

    public function update(Request $request, Post $post)
    {
        $user = $request->user();
        
        // Check if user owns the post or is team member with edit permissions
        if ($post->user_id !== $user->id && (!$post->team_id || !$post->team->canUserEdit($user->id))) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        if ($post->status === 'published') {
            return response()->json(['message' => 'Cannot edit published posts'], Response::HTTP_BAD_REQUEST);
        }

        $request->validate([
            'content' => 'sometimes|required|string|max:3000',
            'schedule_time' => 'sometimes|required|date|after:now',
            'account_id' => 'sometimes|nullable|exists:accounts,id',
            'image_path' => 'sometimes|nullable|string',
            'hashtags' => 'sometimes|nullable|array',
            'tone' => 'sometimes|nullable|string|in:professional,casual,friendly,humorous,formal',
        ]);

        $oldScheduleTime = $post->schedule_time;
        $post->update($request->only(['content', 'schedule_time', 'account_id', 'image_path', 'hashtags', 'tone']));

        // Validate content constraints
        $validationErrors = $post->validateContent();

        // Send notification for post update
        if ($request->has('schedule_time') && $oldScheduleTime != $post->schedule_time) {
            $newScheduleTime = \Carbon\Carbon::parse($post->schedule_time);
            \App\Http\Controllers\Api\NotificationController::sendInfo(
                $user->id,
                'Post Updated',
                "Your post schedule has been updated to {$newScheduleTime->format('M j, Y g:i A')}",
                'post',
                ['post_id' => $post->id]
            );
        }

        return response()->json([
            'data' => $post->load(['account', 'team']),
            'validation_errors' => $validationErrors,
        ]);
    }

    public function destroy(Request $request, Post $post)
    {
        $user = $request->user();
        
        // Check if user owns the post or is team member with edit permissions
        if ($post->user_id !== $user->id && (!$post->team_id || !$post->team->canUserEdit($user->id))) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        if ($post->status === 'published') {
            return response()->json(['message' => 'Cannot delete published posts'], Response::HTTP_BAD_REQUEST);
        }

        $postId = $post->id;
        $post->delete();

        // Send notification for post deletion
        \App\Http\Controllers\Api\NotificationController::sendWarning(
            $user->id,
            'Post Deleted',
            'Your scheduled post has been deleted',
            'post',
            ['post_id' => $postId]
        );

        return response()->json(['message' => 'Post deleted successfully']);
    }

    /**
     * Approve a post
     */
    public function approve(Request $request, Post $post): JsonResponse
    {
        $user = $request->user();
        
        // Check if user can approve posts
        if ($post->user_id === $user->id) {
            return response()->json(['message' => 'Cannot approve your own posts'], 400);
        }

        if (!$post->team_id || !$post->team->canUserApprove($user->id)) {
            return response()->json(['message' => 'Unauthorized to approve posts'], 403);
        }

        if ($post->approval_status !== 'pending_approval') {
            return response()->json(['message' => 'Post is not pending approval'], 400);
        }

        $post->approve($user->id, $request->comments);

        // Send notification to post creator
        \App\Http\Controllers\Api\NotificationController::sendSuccess(
            $post->user_id,
            'Post Approved',
            "Your post has been approved by {$user->name}",
            'post',
            ['post_id' => $post->id, 'approved_by' => $user->id]
        );

        return response()->json(['message' => 'Post approved successfully']);
    }

    /**
     * Reject a post
     */
    public function reject(Request $request, Post $post): JsonResponse
    {
        $user = $request->user();
        
        // Check if user can approve posts
        if ($post->user_id === $user->id) {
            return response()->json(['message' => 'Cannot reject your own posts'], 400);
        }

        if (!$post->team_id || !$post->team->canUserApprove($user->id)) {
            return response()->json(['message' => 'Unauthorized to reject posts'], 403);
        }

        if ($post->approval_status !== 'pending_approval') {
            return response()->json(['message' => 'Post is not pending approval'], 400);
        }

        $post->reject($user->id, $request->comments);

        // Send notification to post creator
        \App\Http\Controllers\Api\NotificationController::sendWarning(
            $post->user_id,
            'Post Rejected',
            "Your post has been rejected by {$user->name}",
            'post',
            ['post_id' => $post->id, 'rejected_by' => $user->id]
        );

        return response()->json(['message' => 'Post rejected successfully']);
    }

    /**
     * Get posts pending approval
     */
    public function pendingApproval(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $query = Post::where('approval_status', 'pending_approval')
            ->whereHas('team', function ($q) use ($user) {
                $q->whereHas('members', function ($memberQ) use ($user) {
                    $memberQ->where('user_id', $user->id);
                });
            })
            ->with(['account', 'user', 'team']);

        $posts = $query->orderBy('schedule_time', 'asc')->paginate(20);

        return response()->json(['data' => $posts]);
    }

    /**
     * Get analytics for posts
     */
    public function analytics(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = $user->posts()->where('status', 'published');

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('published_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('published_at', '<=', $request->end_date);
        }

        $posts = $query->get();

        $analytics = [
            'total_posts' => $posts->count(),
            'total_engagement' => $posts->sum('total_engagement'),
            'average_engagement_rate' => $posts->avg('engagement_rate'),
            'platform_breakdown' => $posts->groupBy('account.platform')->map(function ($platformPosts) {
                return [
                    'count' => $platformPosts->count(),
                    'total_engagement' => $platformPosts->sum('total_engagement'),
                    'avg_engagement_rate' => $platformPosts->avg('engagement_rate'),
                ];
            }),
            'top_performing_posts' => $posts->sortByDesc('total_engagement')->take(5),
        ];

        return response()->json(['data' => $analytics]);
    }
}

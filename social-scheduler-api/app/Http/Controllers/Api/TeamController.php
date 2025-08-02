<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeamController extends Controller
{
    /**
     * Get user's teams
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        
        $teams = Team::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['members.user', 'owner'])->get();

        return response()->json([
            'data' => $teams
        ]);
    }

    /**
     * Create a new team
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'sometimes|string|max:1000',
            'subdomain' => 'sometimes|string|max:255|unique:teams,subdomain',
        ]);

        $user = Auth::user();

        DB::transaction(function () use ($request, $user) {
            $team = Team::create([
                'owner_id' => $user->id,
                'name' => $request->name,
                'description' => $request->description,
                'subdomain' => $request->subdomain,
            ]);

            // Add owner as team member
            TeamMember::create([
                'team_id' => $team->id,
                'user_id' => $user->id,
                'role' => 'owner',
            ]);
        });

        return response()->json([
            'message' => 'Team created successfully'
        ], 201);
    }

    /**
     * Get team details
     */
    public function show(int $id): JsonResponse
    {
        $user = Auth::user();
        
        $team = Team::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['members.user', 'owner', 'posts'])->findOrFail($id);

        return response()->json([
            'data' => $team
        ]);
    }

    /**
     * Update team
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:1000',
            'subdomain' => 'sometimes|string|max:255|unique:teams,subdomain,' . $id,
            'branding' => 'sometimes|array',
        ]);

        $user = Auth::user();
        $team = Team::findOrFail($id);

        // Check if user can manage team
        if (!$team->canUserEdit($user->id)) {
            return response()->json([
                'message' => 'Unauthorized to update team'
            ], 403);
        }

        $team->update($request->only(['name', 'description', 'subdomain', 'branding']));

        return response()->json([
            'message' => 'Team updated successfully',
            'data' => $team
        ]);
    }

    /**
     * Invite member to team
     */
    public function inviteMember(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'role' => 'required|string|in:admin,editor,viewer',
        ]);

        $user = Auth::user();
        $team = Team::findOrFail($id);

        // Check if user can manage team
        if (!$team->canUserEdit($user->id)) {
            return response()->json([
                'message' => 'Unauthorized to invite members'
            ], 403);
        }

        $invitee = User::where('email', $request->email)->first();
        
        if (!$invitee) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        if ($team->hasMember($invitee->id)) {
            return response()->json([
                'message' => 'User is already a team member'
            ], 400);
        }

        TeamMember::create([
            'team_id' => $team->id,
            'user_id' => $invitee->id,
            'role' => $request->role,
        ]);

        return response()->json([
            'message' => 'Member invited successfully'
        ]);
    }

    /**
     * Update member role
     */
    public function updateMemberRole(Request $request, int $teamId, int $memberId): JsonResponse
    {
        $request->validate([
            'role' => 'required|string|in:admin,editor,viewer',
        ]);

        $user = Auth::user();
        $team = Team::findOrFail($teamId);

        // Check if user can manage team
        if (!$team->canUserEdit($user->id)) {
            return response()->json([
                'message' => 'Unauthorized to update member roles'
            ], 403);
        }

        $member = TeamMember::where('team_id', $teamId)
            ->where('id', $memberId)
            ->firstOrFail();

        // Prevent changing owner role
        if ($member->role === 'owner') {
            return response()->json([
                'message' => 'Cannot change owner role'
            ], 400);
        }

        $member->update(['role' => $request->role]);

        return response()->json([
            'message' => 'Member role updated successfully'
        ]);
    }

    /**
     * Remove member from team
     */
    public function removeMember(int $teamId, int $memberId): JsonResponse
    {
        $user = Auth::user();
        $team = Team::findOrFail($teamId);

        // Check if user can manage team
        if (!$team->canUserEdit($user->id)) {
            return response()->json([
                'message' => 'Unauthorized to remove members'
            ], 403);
        }

        $member = TeamMember::where('team_id', $teamId)
            ->where('id', $memberId)
            ->firstOrFail();

        // Prevent removing owner
        if ($member->role === 'owner') {
            return response()->json([
                'message' => 'Cannot remove team owner'
            ], 400);
        }

        $member->delete();

        return response()->json([
            'message' => 'Member removed successfully'
        ]);
    }

    /**
     * Leave team
     */
    public function leaveTeam(int $id): JsonResponse
    {
        $user = Auth::user();
        $team = Team::findOrFail($id);

        $member = TeamMember::where('team_id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Prevent owner from leaving
        if ($member->role === 'owner') {
            return response()->json([
                'message' => 'Team owner cannot leave. Transfer ownership first.'
            ], 400);
        }

        $member->delete();

        return response()->json([
            'message' => 'Left team successfully'
        ]);
    }

    /**
     * Get team posts
     */
    public function getPosts(int $id): JsonResponse
    {
        $user = Auth::user();
        $team = Team::findOrFail($id);

        // Check if user is team member
        if (!$team->hasMember($user->id)) {
            return response()->json([
                'message' => 'Unauthorized to view team posts'
            ], 403);
        }

        $posts = $team->posts()
            ->with(['account', 'user', 'approvedBy'])
            ->orderBy('schedule_time', 'desc')
            ->paginate(20);

        return response()->json([
            'data' => $posts
        ]);
    }
} 
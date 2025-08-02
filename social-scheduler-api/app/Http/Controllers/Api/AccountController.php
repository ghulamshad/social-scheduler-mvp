<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $accounts = $request->user()
            ->accounts()
            ->orderBy('platform')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $accounts]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'platform' => 'required|string|in:twitter,facebook,instagram,linkedin',
            'username' => 'required|string|max:255',
            'avatar_url' => 'nullable|url',
            'platform_data' => 'nullable|array',
        ]);

        // Check for duplicate account
        $existingAccount = $request->user()->accounts()
            ->where('platform', $request->platform)
            ->where('username', $request->username)
            ->first();

        if ($existingAccount) {
            return response()->json([
                'message' => 'Account already exists for this platform and username'
            ], Response::HTTP_CONFLICT);
        }

        $account = $request->user()->accounts()->create([
            'name' => $request->name,
            'platform' => $request->platform,
            'username' => $request->username,
            'avatar_url' => $request->avatar_url,
            'platform_data' => $request->platform_data,
            'is_active' => true,
        ]);

        return response()->json($account, Response::HTTP_CREATED);
    }

    public function show(Request $request, Account $account)
    {
        if ($account->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        return response()->json($account);
    }

    public function update(Request $request, Account $account)
    {
        if ($account->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'username' => 'sometimes|required|string|max:255',
            'avatar_url' => 'sometimes|nullable|url',
            'is_active' => 'sometimes|boolean',
            'platform_data' => 'sometimes|nullable|array',
        ]);

        // Check for duplicate account if username is being changed
        if ($request->has('username') && $request->username !== $account->username) {
            $existingAccount = $request->user()->accounts()
                ->where('platform', $account->platform)
                ->where('username', $request->username)
                ->where('id', '!=', $account->id)
                ->first();

            if ($existingAccount) {
                return response()->json([
                    'message' => 'Account already exists for this platform and username'
                ], Response::HTTP_CONFLICT);
            }
        }

        $account->update($request->only(['name', 'username', 'avatar_url', 'is_active', 'platform_data']));

        return response()->json($account);
    }

    public function destroy(Request $request, Account $account)
    {
        if ($account->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        // Check if account has scheduled posts
        $scheduledPosts = $account->posts()->where('status', 'scheduled')->count();
        if ($scheduledPosts > 0) {
            return response()->json([
                'message' => 'Cannot delete account with scheduled posts'
            ], Response::HTTP_BAD_REQUEST);
        }

        $account->delete();

        return response()->json(['message' => 'Account deleted successfully']);
    }
}

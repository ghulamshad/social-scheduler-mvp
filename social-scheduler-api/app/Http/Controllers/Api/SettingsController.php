<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    /**
     * Get user settings
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $category = $request->get('category');
        
        $settings = Setting::getSettingsArray($user->id, $category);
        
        return response()->json([
            'data' => $settings
        ]);
    }

    /**
     * Store a new setting
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $request->validate([
            'key' => 'required|string|max:255',
            'value' => 'required',
            'type' => 'required|in:string,boolean,integer,json',
            'category' => 'required|in:general,notifications,api,theme',
            'description' => 'nullable|string'
        ]);
        
        $setting = Setting::updateOrCreate(
            ['user_id' => $user->id, 'key' => $request->key],
            [
                'value' => $request->value,
                'type' => $request->type,
                'category' => $request->category,
                'description' => $request->description
            ]
        );
        
        return response()->json([
            'message' => 'Setting saved successfully',
            'data' => $setting
        ], 201);
    }

    /**
     * Update multiple settings
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $request->validate([
            'settings' => 'required|array',
            'category' => 'required|in:general,notifications,api,theme'
        ]);
        
        Setting::setMultipleSettings($user->id, $request->settings, $request->category);
        
        return response()->json([
            'message' => 'Settings updated successfully'
        ]);
    }

    /**
     * Get setting by key
     */
    public function show(string $key): JsonResponse
    {
        $user = Auth::user();
        
        $setting = Setting::where('user_id', $user->id)
            ->where('key', $key)
            ->first();
            
        if (!$setting) {
            return response()->json([
                'message' => 'Setting not found'
            ], 404);
        }
        
        return response()->json([
            'data' => $setting
        ]);
    }

    /**
     * Update setting
     */
    public function update(Request $request, string $key): JsonResponse
    {
        $user = Auth::user();
        
        $request->validate([
            'value' => 'required',
            'type' => 'sometimes|in:string,boolean,integer,json',
            'category' => 'sometimes|in:general,notifications,api,theme',
            'description' => 'nullable|string'
        ]);
        
        $setting = Setting::where('user_id', $user->id)
            ->where('key', $key)
            ->first();
            
        if (!$setting) {
            return response()->json([
                'message' => 'Setting not found'
            ], 404);
        }
        
        $setting->update([
            'value' => $request->value,
            'type' => $request->type ?? $setting->type,
            'category' => $request->category ?? $setting->category,
            'description' => $request->description ?? $setting->description
        ]);
        
        return response()->json([
            'message' => 'Setting updated successfully',
            'data' => $setting
        ]);
    }

    /**
     * Delete setting
     */
    public function destroy(string $key): JsonResponse
    {
        $user = Auth::user();
        
        $setting = Setting::where('user_id', $user->id)
            ->where('key', $key)
            ->first();
            
        if (!$setting) {
            return response()->json([
                'message' => 'Setting not found'
            ], 404);
        }
        
        $setting->delete();
        
        return response()->json([
            'message' => 'Setting deleted successfully'
        ]);
    }

    /**
     * Get settings by category
     */
    public function getByCategory(string $category): JsonResponse
    {
        $user = Auth::user();
        
        $settings = Setting::where('user_id', $user->id)
            ->where('category', $category)
            ->get();
            
        return response()->json([
            'data' => $settings
        ]);
    }

    /**
     * Reset settings to defaults
     */
    public function resetDefaults(): JsonResponse
    {
        $user = Auth::user();
        
        // Delete existing settings
        Setting::where('user_id', $user->id)->delete();
        
        // Set default settings
        $defaults = [
            'theme' => 'light',
            'notifications_enabled' => true,
            'email_notifications' => true,
            'timezone' => 'UTC',
            'language' => 'en',
            'auto_refresh' => true,
            'refresh_interval' => 30,
            'show_analytics' => true,
            'compact_mode' => false,
        ];
        
        Setting::setMultipleSettings($user->id, $defaults, 'general');
        
        return response()->json([
            'message' => 'Settings reset to defaults',
            'data' => $defaults
        ]);
    }
} 
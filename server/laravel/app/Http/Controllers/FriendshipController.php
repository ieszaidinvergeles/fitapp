<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Manages user-to-user social connections (friends/partners).
 */
class FriendshipController extends Controller
{
    /**
     * Returns a list of the authenticated user's friends.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $friendIds = DB::table('user_partners')
                ->where('primary_user_id', $user->id)
                ->pluck('partner_user_id')
                ->merge(
                    DB::table('user_partners')
                        ->where('partner_user_id', $user->id)
                        ->pluck('primary_user_id')
                )
                ->unique()
                ->toArray();
            
            $friends = User::whereIn('id', $friendIds)->get();

            return response()->json([
                'result' => $friends->map(fn($u) => [
                    'id' => $u->id,
                    'username' => $u->username,
                    'full_name' => $u->full_name,
                    'profile_photo_url' => $u->profile_photo_url ? url("/api/v1/users/{$u->id}/photo") : null,
                ]),
                'message' => ['general' => 'OK']
            ]);
        } catch (\Exception $e) {
            return response()->json(['result' => false, 'message' => ['general' => $e->getMessage()]], 500);
        }
    }

    /**
     * Searches for other CLIENT users to add as friends.
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->input('q');
            $me = $request->user();

            if (!$me) {
                return response()->json(['result' => false, 'message' => ['general' => 'Unauthenticated']], 401);
            }

            // 1. Get current friend IDs to exclude them
            $friendIds = DB::table('user_partners')
                ->where('primary_user_id', $me->id)
                ->pluck('partner_user_id')
                ->merge(
                    DB::table('user_partners')
                        ->where('partner_user_id', $me->id)
                        ->pluck('primary_user_id')
                )
                ->unique()
                ->toArray();

            // 2. Build query
            $users = User::whereIn('role', ['client', 'user_online'])
                ->where('id', '!=', $me->id)
                ->whereNotIn('id', $friendIds)
                ->when($query, function($q) use ($query) {
                    $q->where(function($sq) use ($query) {
                        $sq->where('username', 'like', "%{$query}%")
                           ->orWhere('full_name', 'like', "%{$query}%");
                    });
                })
                ->limit(20)
                ->get();

            return response()->json([
                'result' => $users->map(fn($u) => [
                    'id' => $u->id,
                    'username' => $u->username,
                    'full_name' => $u->full_name,
                    'profile_photo_url' => $u->profile_photo_url ? url("/api/v1/users/{$u->id}/photo") : null,
                    'is_friend' => false,
                ]),
                'message' => ['general' => 'OK']
            ]);
        } catch (\Exception $e) {
            return response()->json(['result' => false, 'message' => ['general' => $e->getMessage()]], 500);
        }
    }

    /**
     * Toggles a friendship connection with another user.
     */
    public function toggle(Request $request, int $id): JsonResponse
    {
        try {
            $me = $request->user();
            if ($me->id === $id) {
                return response()->json(['result' => false, 'message' => ['general' => 'You cannot friend yourself.']], 400);
            }

            $exists = DB::table('user_partners')
                ->where(function($q) use ($me, $id) {
                    $q->where('primary_user_id', $me->id)->where('partner_user_id', $id);
                })
                ->orWhere(function($q) use ($me, $id) {
                    $q->where('primary_user_id', $id)->where('partner_user_id', $me->id);
                })
                ->first();

            if ($exists) {
                DB::table('user_partners')->where('id', $exists->id)->delete();
                $msg = 'Friend removed.';
            } else {
                DB::table('user_partners')->insert([
                    'primary_user_id' => $me->id,
                    'partner_user_id' => $id,
                    'linked_at' => now(),
                ]);
                $msg = 'Friend added.';
            }

            return response()->json([
                'result' => true,
                'message' => ['general' => $msg]
            ]);
        } catch (\Exception $e) {
            return response()->json(['result' => false, 'message' => ['general' => $e->getMessage()]], 500);
        }
    }

    /**
     * Returns public profile data for another user.
     */
    public function showProfile(Request $request, int $id): JsonResponse
    {
        try {
            $user = User::with(['settings', 'latestBodyMetric', 'currentGym', 'membershipPlan'])->findOrFail($id);
            $me = $request->user();
            $settings = $user->settings;

            // Check friendship status
            $isFriend = DB::table('user_partners')
                ->where(function($q) use ($me, $id) {
                    $q->where('primary_user_id', $me->id)->where('partner_user_id', $id);
                })
                ->orWhere(function($q) use ($me, $id) {
                    $q->where('primary_user_id', $id)->where('partner_user_id', $me->id);
                })
                ->exists();

            $data = [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'role' => $user->role,
                'profile_photo_url' => $user->profile_photo_url ? url("/api/v1/users/{$user->id}/photo") : null,
                'birth_date' => $user->birth_date?->format('Y-m-d'),
                'member_since' => $user->created_at?->format('Y-m-d'),
                'membership_status' => $user->membership_status,
                'membership_plan' => $user->membershipPlan ? [
                    'name' => $user->membershipPlan->name,
                    'type' => $user->membershipPlan->type,
                ] : null,
                'gym' => $user->currentGym ? [
                    'name' => $user->currentGym->name,
                    'city' => $user->currentGym->city,
                ] : null,
                'is_friend' => $isFriend,
                'is_me' => $me->id === $user->id,
                'stats' => $settings?->share_workout_stats ? [
                    'completed_classes' => $user->bookings()->where('status', 'attended')->count(),
                    'total_bookings' => $user->bookings()->count(),
                ] : null,
                'metrics' => $settings?->share_body_metrics ? $user->latestBodyMetric : null,
                'privacy' => [
                    'stats_public' => (bool)($settings?->share_workout_stats),
                    'metrics_public' => (bool)($settings?->share_body_metrics),
                ]
            ];

            return response()->json([
                'result' => $data,
                'message' => ['general' => 'OK']
            ]);
        } catch (\Exception $e) {
            return response()->json(['result' => false, 'message' => ['general' => $e->getMessage()]], 500);
        }
    }
}

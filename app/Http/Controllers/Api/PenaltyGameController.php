<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenaltyGameSession;
use App\Models\PenaltyGameStats;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PenaltyGameController extends Controller
{
    /**
     * start game baru
     */
    public function startGame(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $session = PenaltyGameSession::create([
                'user_id' => $user->id,
                'started_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Game session started',
                'data' => [
                    'session_id' => $session->id,
                    'started_at' => $session->started_at,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start game',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * record gol apa kaga
     */
    public function recordShot(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|exists:penalty_game_sessions,id',
            'is_goal' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            $session = PenaltyGameSession::where('id', $request->session_id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            // cek sesi dah beres belom
            if ($session->ended_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'This game session has already ended'
                ], 400);
            }

            DB::beginTransaction();

            $session->total_shots += 1;
            if ($request->is_goal) {
                $session->goals_scored += 1;
            }
            $session->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Shot recorded',
                'data' => [
                    'session_id' => $session->id,
                    'goals_scored' => $session->goals_scored,
                    'total_shots' => $session->total_shots,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to record shot',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * sesi end game dan kalkulasi poin
     */
    public function endGame(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|exists:penalty_game_sessions,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            $session = PenaltyGameSession::where('id', $request->session_id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            if ($session->ended_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'This game session has already ended'
                ], 400);
            }

            DB::beginTransaction();

            // kalkulasi final stats
            $session->accuracy_percentage = $session->calculateAccuracy();
            $session->points_earned = $session->calculatePoints();
            $session->ended_at = now();
            $session->save();

            // apdetatau buat user stats
            $stats = PenaltyGameStats::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'total_points' => 0,
                    'total_goals' => 0,
                    'total_shots' => 0,
                    'games_played' => 0,
                    'best_score' => 0,
                    'current_streak' => 0,
                    'best_streak' => 0,
                ]
            );

            $stats->updateAfterGame($session);

            // apdet user total penalty points
            $user->penalty_points = $stats->total_points;
            $user->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Game ended successfully',
                'data' => [
                    'session' => [
                        'goals_scored' => $session->goals_scored,
                        'total_shots' => $session->total_shots,
                        'accuracy' => $session->accuracy_percentage,
                        'points_earned' => $session->points_earned,
                    ],
                    'stats' => [
                        'total_points' => $stats->total_points,
                        'total_goals' => $stats->total_goals,
                        'games_played' => $stats->games_played,
                        'best_score' => $stats->best_score,
                        'current_streak' => $stats->current_streak,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to end game',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get leaderboard.
     */
    public function getLeaderboard(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            $leaderboard = PenaltyGameStats::with('user:id,name,email')
                ->orderBy('total_points', 'desc')
                ->paginate($perPage);

            $data = $leaderboard->map(function ($stats, $index) use ($page, $perPage) {
                return [
                    'rank' => ($page - 1) * $perPage + $index + 1,
                    'user' => [
                        'id' => $stats->user->id,
                        'name' => $stats->user->name,
                    ],
                    'total_points' => $stats->total_points,
                    'total_goals' => $stats->total_goals,
                    'games_played' => $stats->games_played,
                    'best_score' => $stats->best_score,
                    'accuracy' => $stats->accuracy,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $leaderboard->currentPage(),
                    'last_page' => $leaderboard->lastPage(),
                    'per_page' => $leaderboard->perPage(),
                    'total' => $leaderboard->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get leaderboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user stats.
     */
    public function getUserStats(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $stats = PenaltyGameStats::where('user_id', $user->id)->first();

            if (!$stats) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'total_points' => 0,
                        'total_goals' => 0,
                        'total_shots' => 0,
                        'games_played' => 0,
                        'best_score' => 0,
                        'accuracy' => 0,
                        'current_streak' => 0,
                        'best_streak' => 0,
                        'rank' => null,
                    ]
                ]);
            }

            // Get user rank
            $rank = PenaltyGameStats::where('total_points', '>', $stats->total_points)->count() + 1;

            return response()->json([
                'success' => true,
                'data' => [
                    'total_points' => $stats->total_points,
                    'total_goals' => $stats->total_goals,
                    'total_shots' => $stats->total_shots,
                    'games_played' => $stats->games_played,
                    'best_score' => $stats->best_score,
                    'accuracy' => $stats->accuracy,
                    'current_streak' => $stats->current_streak,
                    'best_streak' => $stats->best_streak,
                    'rank' => $rank,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get user stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user game history.
     */
    public function getUserHistory(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $perPage = $request->input('per_page', 10);

            $history = PenaltyGameSession::where('user_id', $user->id)
                ->whereNotNull('ended_at')
                ->orderBy('ended_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $history->items(),
                'pagination' => [
                    'current_page' => $history->currentPage(),
                    'last_page' => $history->lastPage(),
                    'per_page' => $history->perPage(),
                    'total' => $history->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get game history',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
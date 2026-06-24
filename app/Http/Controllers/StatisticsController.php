<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $items = $user->items();

        $byType = (clone $items)->selectRaw('type, count(*) as total')
            ->groupBy('type')->pluck('total', 'type');

        $byStatus = (clone $items)->selectRaw('status, count(*) as total')
            ->groupBy('status')->pluck('total', 'status');

        $byGenre = (clone $items)->whereNotNull('genre')
            ->selectRaw('genre, count(*) as total')
            ->groupBy('genre')->orderByDesc('total')->limit(10)
            ->pluck('total', 'genre');

        $ratings = (clone $items)->whereNotNull('rating')
            ->selectRaw('rating, count(*) as total')
            ->groupBy('rating')->pluck('total', 'rating');

        $year = (int) $request->query('year', now()->year);
        $challenge = $user->readingChallenges()->where('year', $year)->first();

        $finishedThisYear = (clone $items)
            ->where('status', 'done')
            ->whereYear('finished_at', $year)
            ->count();

        return response()->json([
            'data' => [
                'total' => (clone $items)->count(),
                'by_type' => $byType,
                'by_status' => $byStatus,
                'by_genre' => $byGenre,
                'ratings' => $ratings,
                'challenge' => [
                    'year' => $year,
                    'target' => $challenge?->target_count,
                    'completed' => $finishedThisYear,
                    'percentage' => $challenge && $challenge->target_count > 0
                        ? min(100, (int) round($finishedThisYear / $challenge->target_count * 100))
                        : null,
                ],
            ],
        ]);
    }
}

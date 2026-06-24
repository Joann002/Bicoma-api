<?php

namespace App\Http\Controllers;

use App\Http\Resources\ItemResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        // Genres et créateurs les mieux notés (sur les items notés).
        $topGenres = $user->items()
            ->whereNotNull('genre')->whereNotNull('rating')
            ->selectRaw('genre, avg(rating) as score, count(*) as total')
            ->groupBy('genre')->having('score', '>=', 4)
            ->orderByDesc('score')->limit(5)->pluck('genre');

        $topCreators = $user->items()
            ->whereNotNull('creator')->whereNotNull('rating')
            ->selectRaw('creator, avg(rating) as score, count(*) as total')
            ->groupBy('creator')->having('score', '>=', 4)
            ->orderByDesc('score')->limit(5)->pluck('creator');

        // Suggestions : items en wishlist correspondant aux goûts détectés.
        $suggestions = $user->items()
            ->where('status', 'wishlist')
            ->where(function ($q) use ($topGenres, $topCreators) {
                if ($topGenres->isNotEmpty()) {
                    $q->whereIn('genre', $topGenres);
                }
                if ($topCreators->isNotEmpty()) {
                    $q->orWhereIn('creator', $topCreators);
                }
            })
            ->with('tags')
            ->limit(12)
            ->get();

        return response()->json([
            'data' => [
                'top_genres' => $topGenres,
                'top_creators' => $topCreators,
                'suggestions' => ItemResource::collection($suggestions),
            ],
        ]);
    }
}

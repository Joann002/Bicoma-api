<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReadingChallengeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $challenges = $request->user()->readingChallenges()
            ->orderByDesc('year')
            ->get(['id', 'year', 'target_count']);

        return response()->json(['data' => $challenges]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'year' => ['required', 'integer', 'between:2000,2100'],
            'target_count' => ['required', 'integer', 'between:1,1000'],
        ]);

        $challenge = $request->user()->readingChallenges()->updateOrCreate(
            ['year' => $data['year']],
            ['target_count' => $data['target_count']],
        );

        return response()->json([
            'data' => $challenge->only('id', 'year', 'target_count'),
        ], 201);
    }
}

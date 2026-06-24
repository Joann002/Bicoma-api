<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tags = $request->user()->tags()
            ->withCount('items')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['data' => $tags]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50'],
        ]);

        $tag = $request->user()->tags()->firstOrCreate([
            'name' => trim($data['name']),
        ]);

        return response()->json(['data' => $tag->only('id', 'name')], 201);
    }

    public function destroy(Request $request, Tag $tag): \Illuminate\Http\Response
    {
        abort_unless($tag->user_id === $request->user()->id, 403);

        $tag->delete();

        return response()->noContent();
    }
}

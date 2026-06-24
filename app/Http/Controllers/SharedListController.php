<?php

namespace App\Http\Controllers;

use App\Http\Resources\ItemResource;
use App\Models\Item;
use App\Models\SharedList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SharedListController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $lists = $request->user()->sharedLists()
            ->latest()
            ->get(['id', 'token', 'title', 'filters', 'is_active']);

        return response()->json(['data' => $lists]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'filters' => ['nullable', 'array'],
            'filters.type' => ['nullable', Rule::in(Item::TYPES)],
            'filters.status' => ['nullable', Rule::in(Item::STATUSES)],
            'filters.tag' => ['nullable', 'string', 'max:50'],
        ]);

        $list = $request->user()->sharedLists()->create([
            'title' => $data['title'],
            'filters' => $data['filters'] ?? null,
            'is_active' => true,
        ]);

        return response()->json([
            'data' => $list->only('id', 'token', 'title', 'filters', 'is_active'),
        ], 201);
    }

    public function destroy(Request $request, SharedList $sharedList): \Illuminate\Http\Response
    {
        abort_unless($sharedList->user_id === $request->user()->id, 403);

        $sharedList->delete();

        return response()->noContent();
    }

    /**
     * Accès public en lecture seule via le token.
     */
    public function showPublic(string $token): JsonResponse
    {
        $list = SharedList::where('token', $token)->where('is_active', true)->firstOrFail();

        $query = Item::query()
            ->where('user_id', $list->user_id)
            ->with('tags')
            ->latest('added_at');

        $filters = $list->filters ?? [];

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['tag'])) {
            $query->whereHas('tags', fn ($q) => $q->where('name', $filters['tag']));
        }

        return response()->json([
            'data' => [
                'title' => $list->title,
                'owner' => $list->user->name,
                'items' => ItemResource::collection($query->get()),
            ],
        ]);
    }
}

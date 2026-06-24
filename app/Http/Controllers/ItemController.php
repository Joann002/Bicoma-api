<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ItemController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $request->user()->items()->with('tags')->latest('added_at');

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($request->filled('rating')) {
            $query->where('rating', '>=', (int) $request->query('rating'));
        }

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('creator', 'like', "%{$search}%");
            });
        }

        foreach ((array) $request->query('tags', []) as $tag) {
            $query->whereHas('tags', fn ($q) => $q->where('name', $tag));
        }

        return ItemResource::collection($query->paginate((int) $request->query('per_page', 24)));
    }

    public function store(StoreItemRequest $request): ItemResource
    {
        $item = $request->user()->items()->create([
            ...$request->validated(),
            'added_at' => now(),
        ]);

        return ItemResource::make($item->load('tags'));
    }

    public function show(Request $request, Item $item): ItemResource
    {
        $this->authorize('view', $item);

        return ItemResource::make($item->load('tags'));
    }

    public function update(UpdateItemRequest $request, Item $item): ItemResource
    {
        $this->authorize('update', $item);

        $item->update($request->validated());

        return ItemResource::make($item->load('tags'));
    }

    public function destroy(Request $request, Item $item): \Illuminate\Http\Response
    {
        $this->authorize('delete', $item);

        $item->delete();

        return response()->noContent();
    }
}

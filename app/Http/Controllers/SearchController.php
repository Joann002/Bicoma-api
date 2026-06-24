<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Services\Catalog\CatalogManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class SearchController extends Controller
{
    public function __construct(private CatalogManager $catalog) {}

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(Item::TYPES)],
            'q' => ['required', 'string', 'min:2', 'max:255'],
        ]);

        try {
            $results = $this->catalog->for($data['type'])->search($data['q']);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'data' => array_map(fn ($result) => $result->toArray(), $results),
        ]);
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Item
 */
class ItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'creator' => $this->creator,
            'cover_url' => $this->cover_url,
            'status' => $this->status,
            'rating' => $this->rating,
            'notes' => $this->notes,
            'synopsis' => $this->synopsis,
            'genre' => $this->genre,
            'external_id' => $this->external_id,
            'external_source' => $this->external_source,
            'added_at' => $this->added_at?->toIso8601String(),
            'finished_at' => $this->finished_at?->toIso8601String(),
            'tags' => $this->whenLoaded('tags', fn () => $this->tags->pluck('name')),
            'active_loan' => $this->whenLoaded('loans', function () {
                $loan = $this->loans->firstWhere('returned', false);

                return $loan ? LoanResource::make($loan) : null;
            }),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

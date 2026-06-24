<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Loan
 */
class LoanResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'item_id' => $this->item_id,
            'item' => $this->whenLoaded('item', fn () => [
                'id' => $this->item->id,
                'title' => $this->item->title,
                'type' => $this->item->type,
                'cover_url' => $this->item->cover_url,
            ]),
            'borrower_name' => $this->borrower_name,
            'loan_date' => $this->loan_date?->toDateString(),
            'return_date' => $this->return_date?->toDateString(),
            'returned' => $this->returned,
            'returned_at' => $this->returned_at?->toIso8601String(),
            'overdue' => $this->isOverdue(),
        ];
    }
}

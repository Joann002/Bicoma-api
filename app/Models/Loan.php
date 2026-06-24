<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    protected $fillable = [
        'item_id',
        'borrower_name',
        'loan_date',
        'return_date',
        'returned',
        'returned_at',
    ];

    protected function casts(): array
    {
        return [
            'loan_date' => 'date',
            'return_date' => 'date',
            'returned' => 'boolean',
            'returned_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Item, $this> */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function isOverdue(): bool
    {
        return ! $this->returned
            && $this->return_date !== null
            && $this->return_date->isPast();
    }
}

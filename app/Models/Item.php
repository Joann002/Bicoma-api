<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    /** @use HasFactory<\Database\Factories\ItemFactory> */
    use HasFactory;

    public const TYPES = ['book', 'movie', 'series', 'game'];

    public const STATUSES = ['wishlist', 'in_progress', 'done', 'abandoned'];

    protected $fillable = [
        'type',
        'title',
        'creator',
        'cover_url',
        'status',
        'rating',
        'notes',
        'synopsis',
        'genre',
        'external_id',
        'external_source',
        'added_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'added_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsToMany<Tag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /** @return HasMany<Loan, $this> */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }
}

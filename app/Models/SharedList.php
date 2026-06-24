<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SharedList extends Model
{
    protected $fillable = ['user_id', 'token', 'title', 'filters', 'is_active'];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SharedList $list) {
            $list->token ??= Str::random(32);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

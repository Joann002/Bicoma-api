<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingChallenge extends Model
{
    protected $fillable = ['user_id', 'year', 'target_count'];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'target_count' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

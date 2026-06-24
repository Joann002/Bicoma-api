<?php

namespace App\Jobs;

use App\Models\Item;
use App\Services\Catalog\CatalogManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class FetchItemMetadata implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(public int $itemId) {}

    public function handle(CatalogManager $catalog): void
    {
        $item = Item::find($this->itemId);

        if (! $item || ! $item->external_id) {
            return;
        }

        $result = $catalog->for($item->type)->find($item->external_id);

        if ($result === null) {
            return;
        }

        // On ne remplit que les champs encore vides afin de respecter les
        // saisies manuelles de l'utilisateur.
        $item->fill(array_filter([
            'cover_url' => $item->cover_url ?: $result->cover_url,
            'creator' => $item->creator ?: $result->creator,
            'synopsis' => $item->synopsis ?: $result->synopsis,
            'genre' => $item->genre ?: $result->genre,
        ]));

        $item->save();
    }
}

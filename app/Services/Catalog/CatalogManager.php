<?php

namespace App\Services\Catalog;

use InvalidArgumentException;

class CatalogManager
{
    /**
     * Retourne le service de catalogue adapté au type d'item.
     */
    public function for(string $type): CatalogService
    {
        return match ($type) {
            'book' => new OpenLibraryService(),
            'movie' => new TmdbService('movie'),
            'series' => new TmdbService('series'),
            'game' => new RawgService(),
            default => throw new InvalidArgumentException("Type de catalogue non supporté : {$type}"),
        };
    }
}

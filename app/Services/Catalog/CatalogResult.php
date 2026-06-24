<?php

namespace App\Services\Catalog;

/**
 * Résultat normalisé renvoyé par les différentes APIs externes (livres, films,
 * séries, jeux) afin de présenter un format unique au frontend.
 */
class CatalogResult
{
    public function __construct(
        public string $type,
        public string $external_source,
        public string $external_id,
        public string $title,
        public ?string $creator = null,
        public ?string $cover_url = null,
        public ?string $synopsis = null,
        public ?string $genre = null,
        public ?int $year = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'external_source' => $this->external_source,
            'external_id' => $this->external_id,
            'title' => $this->title,
            'creator' => $this->creator,
            'cover_url' => $this->cover_url,
            'synopsis' => $this->synopsis,
            'genre' => $this->genre,
            'year' => $this->year,
        ];
    }
}

<?php

namespace App\Services\Catalog;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class RawgService implements CatalogService
{
    private string $baseUrl;

    private ?string $apiKey;

    private int $ttl;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('catalog.rawg.base_url'), '/');
        $this->apiKey = config('catalog.rawg.api_key');
        $this->ttl = (int) config('catalog.cache_ttl');
    }

    public function search(string $query): array
    {
        $this->ensureConfigured();
        $cacheKey = 'catalog:rawg:search:'.md5($query);

        return Cache::remember($cacheKey, $this->ttl, function () use ($query) {
            $response = Http::timeout(10)->get("{$this->baseUrl}/games", [
                'key' => $this->apiKey,
                'search' => $query,
                'page_size' => 20,
            ]);

            if ($response->failed()) {
                return [];
            }

            return collect($response->json('results', []))
                ->map(fn (array $row) => $this->mapRow($row))
                ->all();
        });
    }

    public function find(string $externalId): ?CatalogResult
    {
        $this->ensureConfigured();
        $cacheKey = 'catalog:rawg:detail:'.md5($externalId);

        return Cache::remember($cacheKey, $this->ttl, function () use ($externalId) {
            $response = Http::timeout(10)->get("{$this->baseUrl}/games/{$externalId}", [
                'key' => $this->apiKey,
            ]);

            if ($response->failed()) {
                return null;
            }

            return $this->mapRow($response->json());
        });
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function mapRow(array $row): CatalogResult
    {
        $description = $row['description_raw'] ?? null;
        $developer = $row['developers'][0]['name'] ?? null;

        return new CatalogResult(
            type: 'game',
            external_source: 'rawg',
            external_id: (string) ($row['id'] ?? ''),
            title: $row['name'] ?? 'Sans titre',
            creator: $developer,
            cover_url: $row['background_image'] ?? null,
            synopsis: $description,
            genre: $row['genres'][0]['name'] ?? null,
            year: isset($row['released']) ? (int) substr((string) $row['released'], 0, 4) : null,
        );
    }

    private function ensureConfigured(): void
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('RAWG_API_KEY manquant. Ajoutez votre clé dans le fichier .env.');
        }
    }
}

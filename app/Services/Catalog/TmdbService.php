<?php

namespace App\Services\Catalog;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TmdbService implements CatalogService
{
    private string $baseUrl;

    private string $imageBaseUrl;

    private ?string $apiKey;

    private int $ttl;

    /**
     * @param  'movie'|'series'  $type
     */
    public function __construct(private string $type = 'movie')
    {
        $this->baseUrl = rtrim((string) config('catalog.tmdb.base_url'), '/');
        $this->imageBaseUrl = rtrim((string) config('catalog.tmdb.image_base_url'), '/');
        $this->apiKey = config('catalog.tmdb.api_key');
        $this->ttl = (int) config('catalog.cache_ttl');
    }

    public function search(string $query): array
    {
        $this->ensureConfigured();
        $endpoint = $this->type === 'series' ? 'tv' : 'movie';
        $cacheKey = "catalog:tmdb:{$endpoint}:search:".md5($query);

        return Cache::remember($cacheKey, $this->ttl, function () use ($endpoint, $query) {
            $response = Http::timeout(10)->get("{$this->baseUrl}/search/{$endpoint}", [
                'api_key' => $this->apiKey,
                'query' => $query,
                'language' => 'fr-FR',
                'include_adult' => false,
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
        $endpoint = $this->type === 'series' ? 'tv' : 'movie';
        $cacheKey = "catalog:tmdb:{$endpoint}:detail:".md5($externalId);

        return Cache::remember($cacheKey, $this->ttl, function () use ($endpoint, $externalId) {
            $response = Http::timeout(10)->get("{$this->baseUrl}/{$endpoint}/{$externalId}", [
                'api_key' => $this->apiKey,
                'language' => 'fr-FR',
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
        $title = $row['title'] ?? $row['name'] ?? 'Sans titre';
        $date = $row['release_date'] ?? $row['first_air_date'] ?? null;
        $genre = $row['genres'][0]['name'] ?? null;

        return new CatalogResult(
            type: $this->type,
            external_source: 'tmdb',
            external_id: (string) ($row['id'] ?? ''),
            title: $title,
            creator: null,
            cover_url: isset($row['poster_path']) ? $this->imageBaseUrl.$row['poster_path'] : null,
            synopsis: $row['overview'] ?? null,
            genre: $genre,
            year: $date ? (int) substr((string) $date, 0, 4) : null,
        );
    }

    private function ensureConfigured(): void
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('TMDB_API_KEY manquant. Ajoutez votre clé dans le fichier .env.');
        }
    }
}

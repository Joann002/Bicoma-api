<?php

namespace App\Services\Catalog;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class OpenLibraryService implements CatalogService
{
    private string $baseUrl;

    private int $ttl;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('catalog.openlibrary.base_url'), '/');
        $this->ttl = (int) config('catalog.cache_ttl');
    }

    public function search(string $query): array
    {
        $cacheKey = 'catalog:openlibrary:search:'.md5($query);

        return Cache::remember($cacheKey, $this->ttl, function () use ($query) {
            $response = Http::timeout(10)->get("{$this->baseUrl}/search.json", [
                'q' => $query,
                'limit' => 20,
                'fields' => 'key,title,author_name,first_publish_year,cover_i,subject',
            ]);

            if ($response->failed()) {
                return [];
            }

            return collect($response->json('docs', []))
                ->map(fn (array $doc) => $this->mapDoc($doc))
                ->all();
        });
    }

    public function find(string $externalId): ?CatalogResult
    {
        $cacheKey = 'catalog:openlibrary:work:'.md5($externalId);

        return Cache::remember($cacheKey, $this->ttl, function () use ($externalId) {
            $key = str_starts_with($externalId, '/works/') ? $externalId : "/works/{$externalId}";
            $response = Http::timeout(10)->get("{$this->baseUrl}{$key}.json");

            if ($response->failed()) {
                return null;
            }

            $data = $response->json();
            $description = $data['description'] ?? null;

            return new CatalogResult(
                type: 'book',
                external_source: 'openlibrary',
                external_id: $externalId,
                title: $data['title'] ?? 'Sans titre',
                creator: null,
                cover_url: isset($data['covers'][0])
                    ? "https://covers.openlibrary.org/b/id/{$data['covers'][0]}-L.jpg"
                    : null,
                synopsis: is_array($description) ? ($description['value'] ?? null) : $description,
                genre: isset($data['subjects'][0]) ? $data['subjects'][0] : null,
            );
        });
    }

    /**
     * @param  array<string, mixed>  $doc
     */
    private function mapDoc(array $doc): CatalogResult
    {
        return new CatalogResult(
            type: 'book',
            external_source: 'openlibrary',
            external_id: $doc['key'] ?? '',
            title: $doc['title'] ?? 'Sans titre',
            creator: isset($doc['author_name']) ? implode(', ', (array) $doc['author_name']) : null,
            cover_url: isset($doc['cover_i'])
                ? "https://covers.openlibrary.org/b/id/{$doc['cover_i']}-M.jpg"
                : null,
            synopsis: null,
            genre: isset($doc['subject'][0]) ? $doc['subject'][0] : null,
            year: isset($doc['first_publish_year']) ? (int) $doc['first_publish_year'] : null,
        );
    }
}

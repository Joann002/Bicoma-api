<?php

namespace App\Services\Catalog;

interface CatalogService
{
    /**
     * Recherche d'items par mots-clés.
     *
     * @return array<int, CatalogResult>
     */
    public function search(string $query): array;

    /**
     * Récupère un item précis par son identifiant externe (pour l'enrichissement).
     */
    public function find(string $externalId): ?CatalogResult;
}

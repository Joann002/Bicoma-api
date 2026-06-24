# Gestionnaire de Collection Personnelle (Bicoma) — Design

Date : 2026-06-24
Statut : approuvé pour implémentation

## Objectif

Centraliser une collection personnelle (livres, films, séries, jeux) : ce qu'on
possède, ce qu'on a terminé, ce qu'on veut découvrir, avec notes, suivi des prêts,
import de métadonnées via APIs externes, statistiques, recommandations et partage
public en lecture seule.

## Stack

- **Backend** : Laravel 13, Sanctum (auth par token), MySQL, queue + cache via base
  de données (`QUEUE_CONNECTION=database`, `CACHE_STORE=database`).
- **Frontend** : Vue 3 + TypeScript, Vue Router, Pinia, Vite, axios.
- Deux dépôts Git distincts : `Bicoma-api` et `Bicoma-front`.

## Modèle de données

```
User (existant) — id, name, email, password

Item
  id, user_id, type (book|movie|series|game), title, creator,
  cover_url, status (wishlist|in_progress|done|abandoned),
  rating (1..5 nullable), notes (text nullable),
  external_id (string nullable), external_source (string nullable),
  synopsis (text nullable), genre (string nullable),
  added_at, finished_at, timestamps

Tag
  id, user_id, name (unique par user), timestamps

ItemTag (pivot) — item_id, tag_id

Loan
  id, item_id, borrower_name, loan_date, return_date (date prévue nullable),
  returned (bool), returned_at (nullable), timestamps

ReadingChallenge
  id, user_id, year, target_count, timestamps (unique user+year)

SharedList
  id, user_id, token (unique), title, filters (json nullable),
  is_active (bool), timestamps
```

Toutes les ressources (sauf l'accès public partagé) sont scoppées à `user_id`.

## API (préfixe `/api`)

Auth (public) :
- `POST /register`, `POST /login`
Auth (protégé Sanctum) :
- `POST /logout`, `GET /me`
- `GET/POST /items`, `GET/PUT/DELETE /items/{item}` (+ filtres
  `type,status,rating,tags[],q`, tri, pagination)
- `GET/POST /tags`, `DELETE /tags/{tag}`
- `GET/POST /loans`, `PUT /loans/{loan}`, `POST /loans/{loan}/return`,
  `DELETE /loans/{loan}`, `GET /loans/overdue`
- `GET /search?type=&q=` → recherche externe (OpenLibrary/TMDB/RAWG)
- `POST /items/{item}/enrich` → relance le Job d'enrichissement
- `GET /stats` → répartition par type/genre/statut, progression du défi
- `GET /challenges`, `POST /challenges` (upsert année)
- `GET /recommendations` → basé sur genres/créateurs les mieux notés
- `GET/POST /shared-lists`, `DELETE /shared-lists/{sharedList}`
Public (sans auth) :
- `GET /public/{token}` → liste partagée en lecture seule

## Services externes

- `OpenLibraryService` — livres, sans clé. `https://openlibrary.org/search.json`
- `TmdbService` — films/séries, clé `TMDB_API_KEY`.
- `RawgService` — jeux, clé `RAWG_API_KEY`.
- Chaque appel passe par `Cache::remember` (TTL configurable) pour respecter les
  limites de taux. Un contrat commun `ExternalCatalog` normalise les résultats
  (`title, creator, cover_url, external_id, synopsis, genre, year`).

## Queue

- `FetchItemMetadata` (Job) : à la création d'un item avec `external_id`, on
  enrichit en arrière-plan (couverture, synopsis, genre) sans bloquer l'UI.
- `QUEUE_CONNECTION=database` ; worker via `php artisan queue:work`.

## Frontend

- Client axios avec token Bearer + intercepteur 401 → logout.
- Stores Pinia : `auth`, `items`, `tags`, `loans`, `stats`.
- Router avec garde d'authentification ; route publique `/share/:token`.
- Vues : Login/Register, **Étagère** (grille responsive, recherche debounce,
  filtres, lazy-load des couvertures), Détail/Édition item, Prêts, Import/recherche
  externe, Statistiques + défi annuel, Recommandations, Liste publique.
- Design conçu avec les skills UI/UX (système de tokens, cartes, états, dark mode).

## Qualité

- Form Requests pour la validation, API Resources pour la sérialisation.
- Policies pour l'isolation par utilisateur.
- Tests Feature PHPUnit (auth, items, filtres, loans, sharing).

## Stratégie de livraison

Construction module par module, un commit ciblé par module dans chaque dépôt
(jamais un seul gros commit), sans `Co-Authored-By`.

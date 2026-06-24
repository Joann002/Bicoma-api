# Bicoma — API

API REST du gestionnaire de collection personnelle **Bicoma** (livres, films, séries, jeux).
Construite avec **Laravel 13**, authentification par token via **Sanctum**, base de données
**MySQL**, files d'attente et cache via la base de données.

Le frontend (SPA Vue 3) se trouve dans le dépôt `Bicoma-front`.

## Fonctionnalités

- Authentification (inscription, connexion, déconnexion) par token Sanctum
- CRUD des items avec filtres (type, statut, note, tags), recherche et pagination
- Tags personnalisés
- Suivi des prêts (emprunteur, dates, retours, retards)
- Import depuis des APIs externes : **Open Library** (livres), **TMDB** (films/séries),
  **RAWG** (jeux), avec mise en cache des réponses
- Enrichissement asynchrone des métadonnées via un **Job** en file d'attente
- Statistiques (répartition par type/statut/genre) et défi annuel
- Recommandations basées sur les genres et créateurs les mieux notés
- Partage de listes en lecture seule via un lien public

## Prérequis

- PHP 8.3+
- Composer
- MySQL 8+

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Créez la base puis renseignez les identifiants MySQL dans `.env` :

```sql
CREATE DATABASE bicoma CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```dotenv
DB_DATABASE=bicoma
DB_USERNAME=root
DB_PASSWORD=
```

Migrez et (optionnellement) chargez les données de démonstration :

```bash
php artisan migrate --seed
```

Le compte de démo est `demo@bicoma.test` / `password`.

### Clés des APIs externes (optionnel)

Open Library ne nécessite aucune clé. Pour les films/séries et les jeux, ajoutez vos clés
dans `.env` :

```dotenv
TMDB_API_KEY=...   # https://www.themoviedb.org/settings/api
RAWG_API_KEY=...   # https://rawg.io/apidocs
```

## Lancement

```bash
php artisan serve            # API sur http://localhost:8000
php artisan queue:work       # worker pour l'enrichissement asynchrone
```

## Tests

```bash
php artisan test
```

Les tests utilisent SQLite en mémoire (aucune configuration MySQL nécessaire).

## Aperçu des routes (`/api`)

| Méthode | Route | Description |
| --- | --- | --- |
| POST | `/register`, `/login` | Authentification |
| POST | `/logout`, GET `/me` | Session courante |
| GET/POST | `/items` | Liste (filtres) / création |
| GET/PUT/DELETE | `/items/{item}` | Détail / mise à jour / suppression |
| POST | `/items/{item}/enrich` | Relance l'enrichissement |
| GET/POST/DELETE | `/tags` | Tags |
| GET/POST/PUT/DELETE | `/loans` | Prêts (+ `/loans/{loan}/return`, `/loans/overdue`) |
| GET | `/search?type=&q=` | Recherche externe |
| GET | `/stats` | Statistiques + défi |
| GET/POST | `/challenges` | Défi annuel |
| GET | `/recommendations` | Recommandations |
| GET/POST/DELETE | `/shared-lists` | Listes partagées |
| GET | `/public/{token}` | Liste publique (lecture seule) |

Le design détaillé est documenté dans
[`docs/superpowers/specs/2026-06-24-collection-manager-design.md`](docs/superpowers/specs/2026-06-24-collection-manager-design.md).

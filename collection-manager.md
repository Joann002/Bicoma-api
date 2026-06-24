# Gestionnaire de Collection Personnelle (livres, films, jeux)

## Objectif

Centraliser ta collection (livres, films, séries, jeux) : ce que tu possèdes, ce que tu as terminé, ce que tu veux découvrir, avec tes notes, le suivi des prêts, et des recommandations basées sur tes goûts.

## Fonctionnalités

**MVP (v1)**

- CRUD Items (titre, type, créateur, statut, note, notes perso)
- Filtres/recherche par type, statut, note, tags
- Wishlist (statut "à découvrir")
- Suivi des prêts (à qui, depuis quand, rappel de retour)

**Évolutions (v2+)**

- Recherche & import automatique via une API externe (Open Library pour les livres, TMDB pour films/séries, RAWG pour les jeux) — récupération auto de la couverture, du synopsis, etc.
- Récupération asynchrone des métadonnées avec **Laravel Queues** (Jobs)
- Mise en cache des réponses API (`Cache::remember`)
- Statistiques (répartition par genre, progression vers un objectif annuel type "50 livres en 2026")
- Recommandations simples basées sur tes notes passées (genres/créateurs les mieux notés)
- Partage en lecture seule d'une liste via un lien public
- Tags personnalisés

## Modèle de données

```
Item
- id, user_id, type (book | movie | series | game), title, creator,
cover_url, status (wishlist | in_progress | done | abandoned),
rating, notes, external_id, added_at, finished_at

Tag
- id, name

ItemTag (pivot)
- item_id, tag_id

Loan (prêt)
- id, item_id, borrower_name, loan_date, return_date, returned (bool)

# v2
ReadingChallenge : id, user_id, year, target_count
```

## Architecture technique

- On continue sur l'architecture API + SPA (Laravel Sanctum + Vue Router + Pinia)
- Nouveautés introduites par ce projet :
  - **Laravel HTTP Client** (`Http::get(...)`) pour interroger des APIs externes gratuites (Open Library, TMDB, RAWG)
  - **Laravel Queues** : la recherche/ajout d'un item lance un Job en arrière-plan pour récupérer les métadonnées sans bloquer l'utilisateur
  - **Cache** Laravel pour éviter de saturer les APIs externes (limites de taux)
  - Vue : interface "étagère" en grille avec recherche en direct (debounce) et lazy loading des images de couverture

## Étapes de développement

1. CRUD Items basique (sans API externe) + filtres/recherche/tags
2. Suivi des prêts avec rappels
3. Intégration d'une première API externe pour la recherche/import (commence par Open Library, la plus simple)
4. Queues pour la récupération asynchrone des métadonnées
5. Statistiques + défi annuel
6. Recommandations simples
7. Partage de liste publique via lien

## Points de difficulté à anticiper

- Gestion des clés API, limites de taux, et mise en cache
- Jobs/Queues asynchrones — nouveau concept, mais essentiel en entreprise
- Interface grille/étagère avec lazy loading d'images

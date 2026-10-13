# Louzir Mode Intime — Documentation du projet

E-commerce **LouzirModeIntime v2.4.0** (monorepo Laravel), installé localement sur XAMPP (Windows).

## Aperçu

| | |
|---|---|
| Projet | `bagisto/bagisto` v2.4.0 |
| Nom | Louzir Mode Intime |
| Branche | `main` (1 commit : `first commit`) |
| Framework | Laravel 12.55.0 / PHP 8.3.35 |
| Base de données | MariaDB 10.4.32 (XAMPP) — base `louzir_mode_intime`, **146 tables** |
| Frontend | Vue 3 + Vite 5 (thèmes compilés dans `public/themes/*/build`) |
| Routes | **488** routes enregistrées |
| Packages | **38** packages dans `packages/Webkul/*` |
| URL boutique | http://127.0.0.1:8000/ |
| URL admin | http://127.0.0.1:8000/admin — `admin@example.com` / `admin123` |

## Sommaire

| Fichier | Contenu |
|---|---|
| [stack.md](./stack.md) | Stack technique complète (runtime, BDD, frontend, tests, CI, Docker) |
| [architecture.md](./architecture.md) | Architecture en Mermaid : monorepo, couches applicatives, cycle d'une requête, pipeline de build |
| [database.md](./database.md) | 146 tables, diagrammes ER Mermaid par domaine, liste complète des relations FK |
| [routes.md](./routes.md) | Carte des routes Mermaid (shop / admin / API / paiements), middlewares |
| [workflows.md](./workflows.md) | Séquences Mermaid : page d'accueil, checkout, facture/expédition, cycle de commande, événements |
| [installation.md](./installation.md) | Installation & commandes pour lancer/développer le projet |

## État actuel de la donnée

| Donnée | État |
|---|---|
| Catégories | 1 seule : `Root` (aucune catégorie fille) → **menu vide** |
| Produits | 0 |
| Canal (channel) | 1 — `Demo store` (USD, locale `en`) |
| Admin | 1 — `admin@example.com` |
| Pages CMS | 10 (About Us, Contact Us…) |
| Thème | `default` — 13 customisations (menu, home page…) |

> **Bug connu** : les catégories ne s'affichent pas et les URL de catégories renvoient 404
> *Route [shop.product_or_category.index] not found* / `NotFoundHttpException`
> car aucune catégorie fille n'existe → voir [installation.md](./installation.md#remplir-la-base).

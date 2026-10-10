# Installation & commandes

## 1. Environnement (déjà configuré sur cette machine)

| Élément | Valeur |
|---|---|
| PHP CLI | **8.3.35** — `C:\xampp\php8.3` (en 1er dans le PATH utilisateur) |
| `php.ini` | `C:\xampp\php8.3\php.ini` (copie de `php.ini-production` + extensions activées) |
| Extensions | `curl, gd, intl, mbstring, openssl, pdo_mysql, fileinfo, exif, zip, soap, calendar, tokenizer…` |
| Apache | XAMPP, port 80 — **charge encore PHP 8.2** (`C:/xampp/php/php8apache2_4.dll`) |
| MySQL | XAMPP MariaDB 10.4.32, port 3306, user `root` sans mot de passe |
| Node | 22.13.1 / npm 10.9.2 |
| `.env` | copie de `.env.example` + `DB_DATABASE=louzir_mode_intime`, `APP_KEY` générée |

## 2. Étapes déjà effectuées

```powershell
composer install                                  # ✅ vendor/ (PHP 8.3)
Copy-Item .env.example .env                       # ✅
php artisan key:generate --force                  # ✅
# CREATE DATABASE louzir_mode_intime CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
php artisan bagisto:install --no-interaction      # ✅ 146 tables + seed de base + storage:link
npm install                                       # ✅
npm run build                                     # ✅ public/build
# thèmes déjà compilés : public/themes/{shop,admin}/default/build
```

## 3. Lancer le projet

```powershell
cd C:\Users\Yessine-PC\Desktop\LouzirModeIntime\louzirModeIntime
php artisan serve          # → http://127.0.0.1:8000
```

| URL | Accès |
|---|---|
| http://127.0.0.1:8000/ | Boutique |
| http://127.0.0.1:8000/admin | Admin — `admin@example.com` / `admin123` |
| http://127.0.0.1:8000/install | Assistant d'installation (déjà utilisé) |

> Ouvrir un **nouveau terminal** : le PATH de la session courante peut encore pointer
> sur l'ancien PHP 8.2 (`C:\xampp\php`).

## 4. Commandes utiles

| Commande | Rôle |
|---|---|
| `php artisan serve` | Serveur dev (port 8000) |
| `php artisan migrate:status` | État des migrations |
| `php artisan migrate:fresh` | **Repartir de zéro** (supprime toutes les tables) |
| `php artisan db:seed` | Seeder de base |
| `php artisan bagisto:install` | Réinstallation complète (drop + migrate + seed) |
| `php artisan bagisto:fake` | **Génère données de démo** (clients, catégories, produits) |
| `php artisan route:list` | 488 routes |
| `php artisan config:clear` / `route:clear` / `view:clear` | Vider les caches |
| `vendor/bin/pint --dirty` | Style de code (Pint) |
| `php artisan test --compact` | Tests Pest |
| `npm run build` | Build ressources racine |

### Recompiler un thème après modification

```powershell
cd packages\Webkul\Shop;   npm run build     # → public/themes/shop/default/build
cd packages\Webkul\Admin;  npm run build     # → public/themes/admin/default/build
```

<a id="remplir-la-base"></a>

## 5. Remplir la base

### Symptômes actuels

| Constat | Valeur |
|---|---|
| Catégories | **1** (`Root`) — aucune catégorie fille |
| Produits | **0** |
| Menu boutique | vide |
| `/ma-categorie` | **404 — Route [shop.product_or_category.index] not found** |

Cause : `bagisto:install` ne seed que la **configuration de base** (canal, locales,
attributs, pages CMS, statuts RMA). Les données de catalogue sont générées à part.

### Option A — données de démo (recommandé pour tester)

```powershell
php artisan bagisto:fake
```

Le commandeur est interactif :

```
Which record would you like to create?
  [0] Create all from the following
  [1] Customers
  [2] Categories
  [3] Products: All
  ...
Please enter the count for customers : 10
Please enter the count for categories : 5
Please enter the count for products : 20
```

Les jobs sont exécutés en `QUEUE_CONNECTION=sync` → tout est créé immédiatement.
Vérifier ensuite : `/api/categories` doit renvoyer plusieurs catégories, le menu s'affiche.

### Option B — créer ses vraies catégories

1. http://127.0.0.1:8000/admin → **Catalog → Categories → Create**
2. Définir le nom, le slug, le **parent** (sous `Root`), activer *Visible*
3. Republier le thème si besoin : **Settings → Theme →** vérifier que le menu
   `header-menu` pointe bien vers l'arbre de catégories

## 6. Problèmes connus

| Problème | Cause | Solution |
|---|---|---|
| Menu vide, 404 sur les catégories | aucune catégorie fille | `php artisan bagisto:fake` ou créer les catégories (§5) |
| `php` = 8.2 dans un vieux terminal | PATH de session obsolète | ouvrir un **nouveau** terminal (`php -v` → 8.3.35) |
| Port 80 = dashboard XAMPP | DocumentRoot = `C:/xampp/htdocs` | soit utiliser `:8000`, soit créer un vhost vers `public/` |
| Port 80 = « unsupported PHP version » | Apache charge PHP 8.2 | pointer `LoadModule php_module` + `PHPINIDir` vers `C:/xampp/php8.3` |
| Assets 404 après modif de `.vue` | thème non recompilé | `npm run build` dans le package concerné |
| Page blanche / config périmée | cache config | `php artisan config:clear && php artisan route:clear && php artisan view:clear` |
| Elasticsearch / Redis absents | non requis en local | `CACHE_STORE=file`, `QUEUE_CONNECTION=sync` |

## 7. Servir sur le port 80 (optionnel)

Ajouter dans `C:\xampp\apache\conf\extra\httpd-xampp.conf` (ou un vhost) :

```apache
LoadModule php_module "C:/xampp/php8.3/php8apache2_4.dll"
PHPINIDir "C:/xampp/php8.3"
```

Puis un VirtualHost :

```apache
<VirtualHost *:80>
    DocumentRoot "C:/Users/Yessine-PC/Desktop/LouzirModeIntime/louzirModeIntime/public"
    ServerName localhost
    <Directory "C:/Users/Yessine-PC/Desktop/LouzirModeIntime/louzirModeIntime/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

et `httpd-vhosts.conf` inclus dans `httpd.conf`. Redémarrer Apache.
`.env` : `APP_URL=http://localhost` est déjà cohérent.

> Nécessite un **redémarrage d'Apache** — à faire hors session de travail.

# LouzirModeIntime

Boutique de lingerie, vêtements de nuit, homewear et accessoires, personnalisée à partir de **Bagisto et Laravel** pour Louzir Mode Intime.

[Ouvrir la boutique de démonstration](https://louzirmodeintime-production.up.railway.app/)

## Fonctionnalités

- Boutique et administration en français par défaut.
- Boutique disponible en français, anglais et arabe, avec affichage de droite à gauche en arabe.
- Dinar tunisien (TND), avec des prix à trois décimales, par exemple `119,900 DT`.
- Adresses tunisiennes avec sélection du gouvernorat, de la municipalité et du code postal.
- Catalogue, stocks, panier et commande en tant qu’invité ou client connecté.
- Accueil, identité visuelle, bandeau promotionnel et couleur du bandeau configurables dans l’administration.
- Formulaires de connexion et d’inscription adaptés aux écrans mobiles.

## Structure

| Dossier | Contenu |
| --- | --- |
| `app/`, `config/` | Application et configuration Laravel |
| `packages/Louzir/` | Modules Bagisto et personnalisations de la boutique |
| `database/migrations/` | Évolution de la base et configuration de la boutique tunisienne |
| `database/seeders/` | Données de démonstration ajoutées explicitement |
| `public/assets/` | Images, logos et visuels de la boutique |
| `tests/`, `packages/Louzir/*/tests/` | Tests de l’application et des modules |

Les namespaces PHP d’origine sont conservés pour assurer la compatibilité des modules. Les personnalisations du projet s’appuient sur le moteur e-commerce Bagisto ; elles ne constituent pas une réécriture complète de ce moteur.

## Installation locale

Prérequis : **PHP 8.3 ou supérieur, Composer 2, Node.js et MySQL**. Les extensions PHP nécessaires sont indiquées dans `composer.json`.

```sh
composer install
cp .env.example .env
php artisan key:generate
```

Configurer les accès à une base locale dans `.env`. Les valeurs par défaut sont :

```dotenv
APP_LOCALE=fr
APP_FALLBACK_LOCALE=fr
APP_CURRENCY=TND
APP_TIMEZONE=Africa/Tunis
INSTALLER_ENABLED=false
```

Pour une nouvelle base, consulter `php artisan list` et les commandes d’installation de cette version de Bagisto. L’installateur web est désactivé par défaut. Ne pas lancer une réinstallation sur une base existante.

```sh
php artisan migrate
php artisan storage:link
php artisan serve
```

Les interfaces des modules possèdent leurs propres fichiers `package.json` et configurations Vite, notamment dans `packages/Louzir/Shop/`, `Admin/` et `Installer/`.

## Catalogue de démonstration

Sur une boutique de test initialisée, avec un canal, une famille d’attributs et un stock actif :

```sh
php artisan db:seed --class=DemoCatalogSeeder
php artisan cache:clear
php artisan db:seed --class=DemoSubcategoriesSeeder
php artisan responsecache:clear
```

Le catalogue contient huit produits avec images, cinq collections et six sous-catégories : Nuisettes, Bodies, Ensembles de lingerie, Pyjamas, Tenues de détente et Bijoux. Les ajouts sont identifiés comme données de test. Les seeders peuvent être relancés sans créer de doublons ni remplacer les modifications existantes des produits.

## Vérifications

```sh
composer validate --no-check-publish
php artisan test
```

Les tests utilisant MySQL nécessitent une base de test distincte et une configuration `.env.testing` adaptée. Les tests de migration `tests/Unit/TunisianStoreDefaultsTest.php` utilisent SQLite en mémoire et nécessitent l’extension `pdo_sqlite`.

Les contrôles de source en intégration continue vérifient le manifeste Composer, la syntaxe PHP et les espaces dans les modifications ; ils n’exécutent pas la suite complète des tests fonctionnels.

## Déploiement

La démonstration est hébergée sur Railway. Les variables d’environnement et les données du canal doivent correspondre aux langues et à la devise souhaitées. Les images téléversées sont conservées dans un volume persistant monté sur `/app/storage/app/public`.

Les fichiers `.env`, sauvegardes de bases, données clients et journaux de tests restent hors du dépôt. Les paiements de démonstration doivent utiliser des comptes de test.

## Technologies et licences

- [Bagisto](https://github.com/bagisto/bagisto) : moteur e-commerce.
- [Laravel](https://laravel.com/) : framework de l’application.

Les mentions de copyright et licences des composants d’origine sont conservées.

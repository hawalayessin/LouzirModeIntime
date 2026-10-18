# LouzirModeIntime

E-commerce storefront customization built on **Bagisto / Laravel**. The source
includes the upstream Webkul modules as well as Louzir-specific storefront content
and configuration. This repository is not a claim that the entire commerce engine
was written from scratch.

## Structure and contribution boundaries

- `packages/Webkul/`: Bagisto modules plus project-specific changes.
- `packages/Webkul/Shop/tests/Feature/HomeContentTest.php`: localized home-content regression tests.
- `packages/Webkul/Shop/tests/Unit/TunisiaMunicipalitiesTest.php`: municipality-related tests.
- `packages/Webkul/Admin/tests/Feature/Configuration/LouzirHomeTest.php`: home configuration coverage.
- `resources/`, `public/`: application and storefront assets.

The presence of an upstream test suite does not establish authorship of every
test or prove that all custom behavior passes. Review project-specific changes
and preserve upstream copyright/license notices.

## Local prerequisites

PHP 8.3+, Composer 2, Node.js and MySQL. The complete PHP extension and package
requirements are in `composer.json`; each Webkul frontend package may have its
own build manifest. Use synthetic products, customer accounts and payments for a
public demonstration.

## Setup

```sh
composer install
cp .env.example .env
php artisan key:generate
```

Configure the local database, mail and payment sandbox settings in `.env` before
running any installer or migrations. Run `php artisan list` to inspect the
installation commands supported by this Bagisto revision. Never run installers
against an existing store without a backup and a reviewed migration plan.
The included Docker Compose file is a development setup and references Laravel
Sail; it requires the corresponding vendor runtime to be installed first.

## Verification

```sh
composer validate --no-check-publish --no-check-lock
php artisan test
```

Database-backed tests require an isolated testing configuration. Review
`phpunit.xml` before running them; never use a live customer database. CI validates
the Composer manifest, syntax and whitespace; it does not claim to run the full
upstream browser/database test suite.

## Upstream attribution

- [Bagisto](https://github.com/bagisto/bagisto) — e-commerce platform by Webkul.
- [Laravel](https://laravel.com/) — underlying application framework.

Keep secrets, production exports, customer details and test logs out of Git.
Previously exposed credentials must be revoked separately from source cleanup.

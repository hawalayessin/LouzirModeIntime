# Catalogue et environnement local

Le site habituel utilise le fichier .env, MariaDB XAMPP sur le port 3306 et la base louzir_mode_intime. Son adresse locale est http://127.0.0.1:8000. Le service Windows MySQL80 utilise un autre dossier de données ; il ne contient pas le catalogue Louzir.

Les vérifications automatiques utilisent uniquement la base hero_carousel_test du serveur isolé sur le port 33317. Son aperçu reste sur http://127.0.0.1:8002. Ne pas utiliser cet aperçu pour vérifier le catalogue habituel. Lancer les serveurs de test avec --no-reload pour conserver les variables d’environnement du processus.

## Intervention sur le catalogue

Le serveur XAMPP a été relancé. La base habituelle contenait 10 produits, dont 8 articles de démonstration, et 8 catégories. Aucun produit n’a été recréé ni supprimé.

Une sauvegarde avant intervention est conservée dans storage/framework/testing/catalog-original-backup.sql. Elle contient le catalogue et les réglages de la base habituelle et n’est pas publiée par le serveur web.

DemoSubcategoriesSeeder a recréé les six sous-catégories de démonstration manquantes et rétabli leurs associations, sans retirer les catégories existantes des produits.

LingerieCategoriesSeeder ajoute une arborescence inspirée de https://lingerierania.com/ : neuf rubriques et 49 sous-catégories. Les définitions sont dans database/data/lingerie-categories.php. Il ne remplace pas les noms, positions ou associations déjà présents. Les catégories et produits de démonstration restent accessibles. Les associations supplémentaires concernent uniquement les références LOUZIR-DEMO connues. Le script peut être relancé sans créer de doublons ; il refuse les collisions de slugs avec une autre branche.

Les catégories se modifient ensuite dans **Catalogue → Catégories**. Le menu lit l’arbre de catégories du canal courant. La flèche développe les enfants ; le nom de catégorie ouvre sa page. Le même composant récursif gère les menus ordinateur et mobile, y compris les niveaux supplémentaires. Une recherche locale filtre les catégories et leurs descendants sans tenir compte des accents ni des majuscules, et développe les branches correspondantes. Le volet et son fond se placent sous la navigation visible, y compris après défilement ou redimensionnement. Les libellés principaux utilisent 14 px et les sous-catégories 13 px.

## Sauvegarde admin

Le formulaire transmet les métadonnées de chaque section une seule fois. Les champs d’accueil partagent une lecture des réglages pendant le rendu. Les définitions sont indexées par nom ; les valeurs enregistrées sont chargées en une requête et les valeurs inchangées ne sont pas réécrites. Les événements existants et l’invalidation du cache sont conservés.

Sur une base isolée, le traitement mesuré d’une sauvegarde inchangée est passé d’environ 14,2 secondes à 23 millisecondes, avec une requête SQL. Cela mesure le traitement serveur de la sauvegarde ; le rechargement de la page et le réseau s’ajoutent à ce temps.

Les tests ConfigurationPerformanceTest et CategoryNavigationTest couvrent l’absence de réécritures inutiles, les métadonnées, l’arborescence, l’idempotence et la conservation des produits. Le scénario tests/browser/categories-dropdown.cjs vérifie le menu sur ordinateur, sur mobile et au clavier sans modifier les données.

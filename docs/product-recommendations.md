# Articles similaires : fonctionnement et exploitation

## Configuration d’abord

Le catalogue existant n’avait ni produits associés ni réglages enregistrés pour ce bloc. Les deux champs historiques de nombre de produits sont conservés dans **Configurer → Catalogue → Produits → Page de visualisation du produit** (`/admin/configuration/catalog/products`). Un nombre explicitement enregistré à zéro continue de masquer le bloc. Ces nombres gardent leur portée globale historique ; les nouveaux paramètres de sélection, de disponibilité et de cache sont propres au canal.

| Réglage | Valeur initiale | Effet |
| --- | --- | --- |
| Nombre de produits associés | 6 | 0 masque le bloc ; maximum 12 |
| Nombre de ventes incitatives | 4 | 0 masque ce second bloc ; maximum 12 |
| Mode de sélection | Mixte | Associations manuelles, puis catégories communes |
| Articles achetables uniquement | Oui | Utilise les règles du type de produit, de gestion du stock et de précommande existantes |
| Cache du classement | 300 secondes | Réglable de 30 à 1800 secondes ; une dispersion de 0 à 30 secondes évite les expirations synchronisées |

Les associations se modifient dans **Catalogue → Produits → Modifier → Produits associés**. Le mode manuel ne complète pas la liste automatiquement. Le mode catégories ignore les associations manuelles. Le mode mixte donne la priorité aux choix du marchand puis complète la liste. Les ventes incitatives restent exclusivement manuelles.

## Pertinence et intégrité

Le classement automatique privilégie la catégorie commune la plus précise, puis le nombre de catégories communes, puis la proximité du prix indexé pour le groupe client actuel. L’identifiant sert de dernier critère stable. La précision est évaluée par l’étendue du nœud dans l’arbre de catégories : une feuille précède une branche générale. La racine du canal n’est pas une preuve de similarité.

Le produit courant et les variantes non visibles individuellement sont exclus. La sélection vérifie le canal, la langue, les URL publiées et les statuts. Les produits et prix retournés sont relus après consultation du cache, y compris le statut EAV : une désactivation n’est pas contournée par un ancien identifiant indexé. Les réglages de stock existants déterminent si un article est achetable ; un article autorisé en précommande peut donc être proposé. En l’absence d’autre article partageant une catégorie, les choix manuels permettent au marchand de proposer des compléments entre catégories.

Le classement est explicable et utilise les données du catalogue. Il ne nécessite ni suivi individuel des visiteurs, ni service externe, ni données de ventes inventées.

## Temps de réponse et trafic

`ProductRecommendations` sépare les requêtes de sélection, la validation et le chargement des modèles. `RecommendationSettings` borne les valeurs et définit les règles de validation serveur. `RecommendationCache` gère le cache et le verrouillage.

Le cache contient uniquement une liste ordonnée d’identifiants, jamais une réponse JSON, un prix client, un favori ou une disponibilité. Son espace est isolé par connexion/base de données, puis par canal, langue, groupe client, article et paramètres de classement. Les clés restent stables : les éditions du catalogue remplacent les versions enregistrées sans créer une nouvelle famille de clés à chaque sauvegarde.

Une modification produit, catégorie, canal ou configuration invalide la génération des classements. Les commandes ne vident pas tous les classements : la disponibilité est revérifiée lors de chaque lecture. Le verrou non bloquant autorise un seul calcul simultané par classement. Une requête concurrente sans classement encore prêt peut recevoir une liste vide pour ce bloc facultatif ; elle ne reste pas bloquée derrière ce calcul. La durée du verrou est limitée à 15 secondes.

Le nombre affiché est borné à 12 et le réservoir de candidats à quatre fois ce nombre. Les modèles sont chargés par lots de la taille affichée ; les lots suivants sont lus seulement si des candidats sont écartés. Les relations utiles sont préchargées. Les avis sont agrégés en SQL sans charger leurs commentaires. Les index existants sur catégories, produits, canaux et prix sont réutilisés ; aucune migration n’est nécessaire.

Les deux endpoints concernés activent une mémorisation des réglages **limitée à la requête HTTP**. Une écriture de configuration efface cette copie locale. Les autres endpoints gardent leur comportement antérieur. Un service persistant, notamment sous Octane, ne conserve donc pas ces valeurs entre visiteurs.

Dans le test de produits simples, l’API chaude effectue **13 requêtes SQL pour 1 article comme pour 12**, sans nouvelle requête de classement. Les types complexes conservent leurs propres règles de prix et de disponibilité. Le coût du premier classement dépend du nombre de produits partageant les catégories : le cache amortit ce travail, et l’isolation du service permet de remplacer ce calcul par un index de recommandations si le catalogue atteint une taille justifiant cette évolution.

Sur la fiche, le chargement commence à 200 px de l’entrée du bloc dans le viewport, une seule fois. Le contenu principal est disponible avant cette requête. Le délai réseau du carrousel est limité à 8 secondes ; une erreur arrête le squelette. Le démontage annule la requête et retire les observateurs et écouteurs. Les cartes du carrousel ont une largeur explicite pour éviter leur réduction à zéro par la mise en page flex.

Pour plusieurs instances de production, utiliser un **Redis partagé** pour le cache et les verrous, avec un préfixe d’application distinct, et dimensionner PHP-FPM et la base selon les mesures de latence et de débit réelles. Le cache fichier reste adapté à l’environnement local ou à un seul hôte. Les changements d’infrastructure ne sont pas appliqués par cette intervention.

## Validation et maintenance

- Régression : 170 tests PHP réussis, puis contrôle ciblé des derniers ajustements de sélection et de chargement.
- Configuration Core : 8 tests de validation et d’isolation de la mémorisation de requête réussis.
- Navigateur : 21 contrôles boutique sur ordinateur/mobile et en cas d’erreur API ; 22 contrôles de sauvegarde native dans l’admin sur base isolée, avec restauration des paramètres testés.
- Concurrence locale : deux processus PHP avec cache fichier, un seul calcul volontairement ralenti à deux secondes ; le second processus revient sans attendre le calcul. Une lecture chaude réutilise ensuite le résultat.

Les scénarios durables sont `ProductRecommendationsTest`, `RecommendationSettingsTest`, `RequestConfigMemoizationTest`, `tests/browser/product-recommendations.cjs` et `tests/browser/product-recommendations-admin.cjs`. Ce dernier exige `RECOMMENDATIONS_ALLOW_WRITES=1` et des articles de test sur la base jetable. Les captures et comptes rendus sont dans `storage/framework/testing/recommendations-*`. Aucun lien manuel ni réglage marchand du catalogue habituel n’a été remplacé.

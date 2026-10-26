# Thème commun de la boutique Louzir

La maquette Stitch `projects/14992411625287139613/screens/668912b4114c46729fc16d9ca189648c` et le fichier « Lingerie Haute Élégance » fourni servent de référence visuelle : blanc cassé `#faf9f7`, blanc `#ffffff`, gris perle `#efeeec`, contours `#e3e2e0`, texte `#1a1c1b`, typographie Manrope et Playfair Display. Aucun prix, stock, composition, avis ou catégorie de la maquette n’est utilisé comme donnée commerciale.

## Réglages administrateur

**Configurer → Général → Accueil Louzir → Style de toute la boutique** gère le fond, le texte, l’accent, les bordures, les polices du texte et des titres et la largeur du contenu. Ces valeurs suivent le canal et s’appliquent à toutes les langues. Les choix de polices sont limités à des valeurs connues ; couleurs et largeur sont validées côté serveur. Le CSS personnalisé de l’administration reste chargé après le thème et peut le compléter.

La même section gère l’image commune des collections et son activation. Une bannière définie dans **Catalogue → Catégories** reste prioritaire. Désactiver l’image commune conserve les bannières propres aux catégories. Les titres et descriptions viennent de la catégorie ; son mode d’affichage détermine si sa description et ses produits apparaissent. Les liens rapides proviennent uniquement de ses sous-catégories actives. Les textes et liens des boutons collections reprennent les réglages du carrousel d’accueil.

Les images, descriptions longues et courtes, prix, stocks, attributs, options et associations restent gérés dans **Catalogue → Produits**. Les réglages de comparaison, favoris, panier, avis, tri, pagination et filtres conservent leurs contrôles existants. Les boutons d’achat des cartes sont visibles au clavier et sur téléphone ; les produits non disponibles gardent leurs boutons désactivés.

## Présentation commune

La palette propose aussi six réglages distincts : surfaces, panneaux et dégradés, fond et texte des boutons, texte secondaire et accent foncé. Ils sont disponibles dans la même section administrateur, par canal. Les dégradés clairs combinent uniquement ces réglages : changer une couleur dans l’admin modifie aussi les dégradés. Les valeurs par défaut remplacent le beige sombre par du gris perle ; les couleurs déjà personnalisées restent prioritaires. Les pages panier et paiement possèdent un bandeau et un récapitulatif ; les espacements sont adaptés aux petits écrans et aux langues RTL.

Les cartes éditoriales de l’accueil et les cartes produits partagent contours, surfaces dégradées, rayon de 16 px et relief discret au survol (perspective, élévation de 4 px, rotation de 1°). Cet effet CSS n’ajoute aucun écouteur de souris et respecte les mouvements réduits ainsi que la désactivation des animations dans l’admin. Les images produit conservent un portrait 4:5. Les listes réservent 80 px avant le footer sur ordinateur et 96 px sur téléphone ; les carrousels associés disposent aussi d’espace pour leur ombre.

Les catégories sans filtres associés utilisent les attributs filtrables généraux de l’admin. Les associations propres à une catégorie restent prioritaires. La vérification d’une collection Eloquent vide utilise `isEmpty()` ; `empty()` sur l’objet ne détectait jamais l’absence de filtres. Catégories et recherche suivent le seuil de 767 px pendant le redimensionnement et libèrent le verrou de défilement d’un volet ouvert. Une erreur réseau affiche un message traduit et une action de relance après au plus huit secondes, sans chargement infini.

Les nouveaux textes sont traduits en français, anglais et arabe dans `shop::storefront`. Les anciens textes anglais livrés par défaut pour les cookies et certains liens du pied de page sont traduits à l’affichage. Les textes personnalisés du marchand sont conservés, sans modification des données enregistrées.

Le composant `components/layouts/storefront-styles.blade.php` centralise les couleurs et les polices pour les catégories, la recherche, les fiches produit, les carrousels associés, le panier, le paiement, les comptes clients, les pages de connexion et les pages CMS. L’interface d’administration conserve son propre thème.

La fiche produit utilise une seule description éditoriale pour toutes les tailles d’écran, avec de vrais paragraphes, titres, listes, images et tableaux. Le contenu riche est affiché sans être interprété comme un template Vue. Les caractéristiques restent issues des attributs administrateur, y compris les fichiers téléchargeables et les images. Les panneaux caractéristiques et avis utilisent les contrôles natifs `details/summary`, accessibles au clavier. Le lien vers les avis ouvre directement leur panneau. Aucun texte de composition ou de garantie n’est inventé.

Les cartes réutilisent l’API produit et les composants existants. Les titres et images ouvrent les vraies fiches ; les montants gardent la devise et les règles de prix du canal. Les images proposent les variantes 150, 300 et 600 px pour éviter des miniatures trop petites sur les écrans denses. Sous 360 px, la grille passe à une colonne. Les filtres mobiles restent dans leur volet et leur déclencheur est un bouton accessible.

## Vérifications

Le sidebar reprend les titres compacts, séparateurs fins, pastilles de couleur et tailles en cases de la référence Stitch. Le compteur « Affichage de X sur Y options » est supprimé. Les volets utilisent `details/summary` et les options de vraies cases à cocher accessibles au clavier. Les choix restent en brouillon jusqu’au bouton **Appliquer les filtres**, qui transmet une copie indépendante des valeurs ; **Tout effacer** restaure immédiatement les résultats. La recherche et la pagination des options sont conservées, avec annulation des anciennes recherches et délai réseau de huit secondes. Les attributs de prix ne lancent plus de requête inutile pour leurs options.

`FilterLabels` traduit les intitulés standards et les couleurs par défaut quand la traduction de la langue active manque. Les traductions et couleurs hexadécimales enregistrées dans **Catalogue → Attributs** restent prioritaires. Les valeurs libres, marques et références de taille ne sont pas inventées ni réécrites. La recherche reconnaît aussi les couleurs traduites de secours et conserve les identifiants de catalogue ; les traductions personnalisées sont cherchées telles quelles. Les traductions sont chargées par lot pour éviter une requête par option.

`FilterTranslationsTest` vérifie FR/EN/AR, la priorité des textes administrateur, la recherche localisée et les couleurs sûres. `tests/browser/filter-sidebar.cjs` couvre les neuf combinaisons FR/EN/AR × 1440/390/320 px, la sélection, l’application, la remise à zéro, le clavier, le RTL et l’absence de compteur.

`CategoryFiltersTest` couvre le repli sur les filtres généraux, les choix propres à une catégorie, les attributs désactivés et la catégorie inconnue. `tests/browser/storefront-cards-filters.cjs` vérifie l’espace avant le footer, les effets des cartes, les mouvements réduits, les changements de largeur sans rechargement, le déverrouillage du défilement et la relance après une réponse 503.

`tests/browser/storefront-palette.cjs` contrôle le panier rempli et le paiement en français, anglais et arabe, à 320, 390 et 1440 px : surfaces colorées, absence de débordement, clés de traduction et sens RTL. Il exige `PALETTE_TEST_ALLOW_WRITES=1` et utilise le serveur de test sur le port 8002.

La suite historique complète `Shop/tests/Feature/Checkout` suppose des adresses avec états, des méthodes de livraison et des taxes qui diffèrent de la configuration tunisienne actuelle. Dans cette base, elle présente 18 échecs (notamment Faker `Unknown format "state"`). Ces résultats doivent rester visibles ; les tests ciblés `TunisiaAddressTest` et le parcours navigateur produit/livraison contrôlent la configuration réellement utilisée, sans modifier les règles métier pour satisfaire ces anciennes hypothèses.

`StorefrontThemeTest` vérifie la publication des réglages sur les différentes pages, les descriptions, la priorité des bannières, les modes d’affichage, le téléversement et la validation. La suite Configuration inclut aussi les contrôles de traduction, de sauvegarde et les réglages existants.

`tests/browser/storefront-theme.cjs` vérifie neuf types de pages en 320, 390, 768 et 1440 px, les polices, les débordements, les cartes, les liens, les filtres et les panneaux produit. `STOREFRONT_TEST_WIDTHS` permet de sélectionner des largeurs pour un contrôle ciblé. `tests/browser/storefront-theme-admin.cjs` vérifie la sauvegarde réelle des couleurs et polices et leur propagation ; il exige `STOREFRONT_TEST_ALLOW_WRITES=1`, utilise la base jetable sur le port 8002 par défaut et restaure les valeurs testées. Le scénario admin-product-delivery vérifie ensuite le parcours réel d’ajout au panier, d’adresse et de livraison sans créer de commande. Les captures `stitch-theme-*.png` sont conservées dans `storage/framework/testing`.

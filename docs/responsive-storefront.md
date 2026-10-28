# Corrections responsive de la boutique

Les catégories de la navbar restent sur une ligne à partir de 1 024 px. Les espacements et la recherche s’adaptent à la largeur disponible. Les libellés longs peuvent être abrégés visuellement ; le nom complet reste dans le lien et son titre. Les sous-menus restent accessibles sans être coupés par un conteneur défilant.

Les cartes de livraison et de paiement réservent une zone pour leur bouton radio, avec des espacements logiques compatibles avec l’arabe. Les champs radio restent accessibles au clavier et le focus entoure la carte. Les prix, méthodes, textes et images continuent à venir des réglages existants de l’administration.

L’aperçu d’une image téléversée remplit son cadre avec `object-fit: cover`, sans déformer la photo. Cela corrige notamment la photo de profil portrait qui occupait seulement la moitié du cadre circulaire.

Les champs de date et d’heure réservent de la place à l’icône du calendrier. Dans les filtres des commandes, les deux bornes s’affichent chacune sur une ligne jusqu’à 480 px ; les raccourcis de période gardent leurs deux colonnes.

## Vérification locale

```sh
node tests/browser/responsive-storefront.cjs
```

Prérequis : application locale démarrée sur `http://127.0.0.1:8000`, Edge installé, dépendances de développement présentes et catalogue avec un produit disponible. Les méthodes express et paiement à la livraison doivent être actives dans l’admin pour ce scénario.

`RESPONSIVE_TEST_URL` permet de choisir un autre serveur local. Le script refuse les hôtes distants. Son helper PHP refuse les environnements autres que `local` ou `testing`.

Le scénario crée un client temporaire sous `example.invalid` et une image portrait synthétique, puis les nettoie en fin de test. Il ouvre le profil, les filtres de commandes et le vrai tunnel de commande, jusqu’à la sélection du paiement. Il ne valide pas de commande. Les médias et comptes existants ne sont pas modifiés.

Les mesures vérifient notamment : navbar de 1 024 à 3 840 px, photo de 320 à 1 440 px, champs de date et absence de chevauchement entre texte et radios de livraison/paiement. Les résultats et captures sont écrits dans `storage/framework/testing/responsive-storefront/`, hors de Git.

Les modifications ne demandent pas de compilation Vite : elles portent sur les templates Blade, leur CSS intégré et des utilitaires déjà présents dans les assets compilés.

Vérification du 29 octobre 2026 : 78 contrôles navigateur réussis, incluant le survol des sous-menus, l’aperçu d’une nouvelle photo, le changement de livraison au clavier, les deux directions d’écriture et les langues français/anglais/arabe. Syntaxe PHP/JavaScript et `git diff --check` valides. La relecture indépendante n’a pas relevé de problème bloquant.

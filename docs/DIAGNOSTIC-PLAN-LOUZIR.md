# Louzir Mode Intime : diagnostic et feuille de route

Date : 10 octobre 2026. Revision examinee : cd286c1.

## Objectif

Livrer une boutique de lingerie personnalisee, exploitable depuis l'admin,
hebergee sur un VPS avec un domaine propre. Conserver le moteur commercial
Bagisto et isoler les personnalisations Louzir pour faciliter la maintenance.

## Perimetre et limites

- Depot trouve dans Desktop/louzirmode/louzirModeIntime ; l'ancien chemin du chat n'existe plus.
- Lecture des configurations, vues, controleurs, paiements, livraison et structure des tests.
- Bagisto declare 2.4.0 dans packages/Webkul/Core/src/Core.php:31.
- Execution CLI : Laravel 12.55.0, PHP 8.3.35.
- GET http://127.0.0.1:8000/ : HTTP 200. Cela ne valide ni le rendu visuel ni le parcours d'achat, et ne prouve pas que le serveur utilise exactement ce checkout.
- Audit Composer du lock, hors dependances de developpement : 66 avis sur 19 bibliotheques.
- Aucun test de commande, paiement ou remboursement execute ; aucune configuration commerciale en base certifiee.
- Aucun changement applicatif effectue pendant ce diagnostic.

## Constats prioritaires

### P1 : dependances a traiter avant exposition publique

`composer audit --locked --no-dev --format=json` retourne des avis concernant :

| Bibliotheque | Nombre d'avis |
| --- | ---: |
| dompdf/dompdf | 6 |
| enshrined/svg-sanitize | 3 |
| guzzlehttp/guzzle | 9 |
| guzzlehttp/psr7 | 4 |
| laravel/framework | 4 |
| league/commonmark | 13 |
| league/flysystem | 1 |
| maatwebsite/excel | 1 |
| paragonie/sodium_compat | 1 |
| phpoffice/phpspreadsheet | 9 |
| phpseclib/phpseclib | 5 |
| setasign/fpdi | 1 |
| symfony/dom-crawler | 1 |
| symfony/http-foundation | 1 |
| symfony/http-kernel | 1 |
| symfony/mailer | 1 |
| symfony/mime | 2 |
| symfony/polyfill-intl-idn | 1 |
| symfony/routing | 2 |

Ce resultat concerne les versions verrouillees, pas une preuve d'exploitation.
Qualifier les avis et mettre a jour les versions compatibles dans une branche,
puis tester notamment PDF, imports, connexion, panier et paiement. Ne pas lancer
une mise a jour globale directement en production. Auditer aussi les dependances JS.

### P1 : l'accueil contourne les personnalisations de l'admin

`packages/Webkul/Shop/src/Http/Controllers/HomeController.php:33` charge les
customizations actives par canal et theme. La vue `Resources/views/home/index.blade.php`
du meme package ne les utilise pas. Elle contient ses propres sections fixes.

Les images stitch-1.jpg a stitch-5.jpg sont choisies dans le code, et les textes
Louzir viennent des traductions. Modifier les sections correspondantes dans
l'admin ne changera donc pas cet accueil personnalise.

Action : rendre les sections depuis les donnees admin avec des composants Louzir.
Reutiliser les types disponibles dans ThemeController (carrousels images,
categories, produits, contenu statique, services, liens) avant d'ajouter des types.

### P2 : le contenu de marque reste partiellement fixe

`packages/Webkul/Shop/src/Resources/views/components/layouts/footer/index.blade.php:21`
contient email, telephone et liens sociaux fixes. Les liens du footer et le
copyright utilisent deja les reglages admin : conserver cette integration.

`config/themes.php` ne declare que le theme default. Les personnalisations sont
directement dans les vues Webkul, ce qui augmente les conflits lors des mises a jour.
Creer un theme Louzir et reserver un module Louzir aux nouvelles fonctions metier.

### P2 : parcours vers les produits incomplet sur l'accueil

Le bouton principal cible #collections (home/index.blade.php:464), puis les
collections sont des articles illustratifs sans lien de categorie (ligne 533 et suivantes).
Ajouter des destinations catalogue et des produits achetables issus de la base.
Le lien Facebook pointe vers une recherche : remplacer par la page officielle.

### P2 : logo encore contraint a une petite hauteur dans ce checkout

`components/layouts/header/desktop/bottom.blade.php:18` contient toujours h-8.
La lecture du canal pour choisir le logo existe deja. Prevoir une taille responsive,
un apercu admin et une gestion des marges de l'image ; eviter un recadrage fixe
calibre pour un seul fichier. Tester un logo horizontal et un logo carre.

### P2 : localisation et montants a valider

La configuration CLI effective indique Bagisto, localhost, Asia/Kolkata et en.
Le debug est OFF et l'environnement est production. Cela n'indique pas les
valeurs du canal en base : les controler separement.

`packages/Webkul/Checkout/src/Cart.php:906` et suivantes arrondissent remises et
totaux a deux decimales. Si les prix ou le prestataire utilisent trois decimales,
verifier toute la chaine : produit, promotion, taxe, livraison, facture,
paiement et remboursement. Tester un montant tel que 29.995 sans correction locale isolee.

### P2 : paiement et livraison locaux non demontres

Le paiement a la livraison et le virement existent dans Payment/src/Config/payment-methods.php.
Shipping/src/Config/carriers.php propose tarif fixe et livraison gratuite.
La recherche dans packages et config n'a trouve aucune integration nommee
Konnect, Paymee, Flouci ou ClicToPay ; cela ne constitue pas un inventaire des services externes.

Choisir le prestataire et le transporteur avant de developper un connecteur.
Ne pas confondre presence d'un module et disponibilite commerciale pour l'entreprise.

## Architecture cible et controle admin

| Domaine | Configuration attendue |
| --- | --- |
| Identite | Logo, favicon, couleurs encadrees, contacts, reseaux |
| Accueil | Images desktop/mobile, textes FR/AR, liens, ordre et activation des sections |
| Catalogue | Categories, variantes taille/bonnet/couleur, photos, composition, entretien |
| Vente | Prix, promotions, stock par variante, livraison, suivi et remboursements |
| Confiance | Guide des tailles, FAQ, boutiques, horaires, pages de politique commerciale |
| Acces | Roles proprietaire, commandes et contenu ; restrictions sur reglages sensibles |

Le controle du contenu doit se faire sans modifier du HTML. Le code definit les
composants et les regles de validation ; l'admin gere les valeurs et leur publication.
L'admin commercial ne remplace pas l'administration technique du VPS.

## Plan a suivre

### Lot 1 : etablir une base fiable

- [ ] Confirmer le dossier canonique et la revision, sauvegarder code, base et medias.
- [ ] Qualifier les avis de securite, appliquer les correctifs compatibles et auditer le JS.
- [ ] Configurer une base de tests distincte avant d'executer les suites existantes.
- [ ] Verifier les licences des photos, polices et extensions ; conserver les notices du code source.

Validation : dependances traitees, application reproductible et restauration testee.

### Lot 2 : rendre le design pilotable

- [ ] Creer le theme Louzir et migrer progressivement les vues personnalisees.
- [ ] Reconnecter les sections d'accueil aux customizations existantes.
- [ ] Exposer contacts, reseaux, boutiques et images dans l'admin.
- [ ] Corriger le logo, relier les collections et afficher de vrais produits.
- [ ] Verifier le mobile, les traductions et le RTL arabe.

Validation : changer une banniere, un logo, un lien et l'ordre des sections depuis
l'admin change effectivement la boutique sans edition du code.

### Lot 3 : construire le catalogue lingerie

- [ ] Definir categories et familles d'attributs ; commencer avec un petit catalogue reel.
- [ ] Configurer tailles, bonnets et couleurs avec SKU, stock et photos par variante.
- [ ] Ajouter guides de tailles par marque si necessaire, matieres et entretien.
- [ ] Fixer la source de verite du stock si les boutiques physiques vendent les memes articles.
- [ ] Prevoir une presentation claire des frais, delais et conditions d'echange.

Validation : une variante indisponible ne peut pas etre vendue et les filtres
permettent de trouver une taille reellement en stock.

### Lot 4 : finaliser la vente en Tunisie

- [ ] Valider FR/AR, TND, fuseau Africa/Tunis et regles d'arrondi.
- [ ] Configurer adresses, telephone +216, zones et tarifs de livraison.
- [ ] Valider paiement a la livraison pour la V1 si retenu ; integrer la carte seulement apres choix du prestataire.
- [ ] Configurer les emails transactionnels avec un service SMTP et un domaine authentifie.
- [ ] Tester commande invite, compte client, annulation, retour et remboursement.
- [ ] Pour la carte : verifier signature des notifications, doublons, montants et echecs ; ne pas marquer paye sur la seule redirection du navigateur.

Validation : une commande complete conserve les memes montants dans panier,
commande, facture et paiement ; le stock et les notifications restent coherents.

### Lot 5 : preparer et tester le VPS

- [ ] Choisir la region, le budget et les ressources apres mesure de charge.
- [ ] Installer une pile compatible : Linux, Nginx, PHP-FPM, base MySQL compatible ; cache et workers selon les besoins mesures.
- [ ] Faire pointer Nginx uniquement vers public/ ; conserver secrets et base hors exposition publique.
- [ ] Configurer domaine, DNS, HTTPS, cookies securises, pare-feu et acces SSH par cle.
- [ ] Configurer workers persistants, scheduler Laravel et compilation des assets du theme/package concerne.
- [ ] Livrer code, base et medias, configurer le lien storage et les caches ; exclure les marqueurs Vite de developpement.
- [ ] Prevoir sauvegardes hors VPS, restauration, alertes de panne et procedure de retour a la version precedente.
- [ ] Activer la double authentification admin deja presente dans le code et tester les roles.

Point de depart de dimensionnement a mesurer : 2-4 vCPU et 4-8 Go RAM pour une
petite boutique, selon trafic, catalogue et services installes. Ce n'est ni un
minimum certifie ni une recommandation d'offre OVH precise. Elasticsearch,
traitements d'images et builds peuvent changer sensiblement le besoin.

Le pays des clients ne determine pas le pays du datacenter. Verifier region,
conditions de commande depuis la Tunisie et extension du domaine avant achat.
Verifier separement domaine, emails et sauvegardes inclus dans l'offre choisie.

Validation : preproduction protegee fonctionnelle, restauration reussie,
assets et medias accessibles, emails recus et commande de bout en bout validee.

### Lot 6 : lancement et exploitation

- [ ] Valider les pages commerciales et obligations applicables avec les personnes competentes.
- [ ] Supprimer les contenus de demonstration et verifier les liens, metadonnees et sitemap.
- [ ] Tester le cache entre deux clients distincts pour exclure toute fuite de panier ou compte.
- [ ] Mesurer les pages et images sur mobile ; corriger erreurs JS, debordements et lenteurs.
- [ ] Mettre en ligne une revision identifiee ; tester une vraie commande controlee.
- [ ] Verifier alertes, emails, stock et commandes apres lancement, puis maintenir les dependances.

## Idees propres a Louzir

Priorite V1 : guide des tailles utile, filtres sur stock disponible, photos par
couleur, composition et entretien, emballage discret si le service est assure,
conseil WhatsApp, retrait boutique si l'organisation du stock le permet.

Plus tard : alerte retour en stock, ensembles coordonnes, fidelite et conseils
personnalises. Ne pas multiplier les modules avant de fiabiliser commande et stock.

## Decisions a prendre ensemble

1. Perimetre V1 : femmes uniquement, ou hommes/enfants aussi ? FR, AR, ou les deux ?
2. Paiement a la livraison, carte, ou les deux ? Transporteur et tarifs envisages ?
3. Stock partage avec Sahloul/Jemmel ou stock web reserve ? Qui le met a jour ?
4. Budget mensuel global et date de lancement cible ? Nombre de produits/variantes ?
5. Quel domaine et qui assurera les mises a jour et sauvegardes apres lancement ?

## References consultees

- Theme Bagisto : https://devdocs.bagisto.com/theme-development/getting-started
- Deploiement : https://devdocs.bagisto.com/getting-started/deployment.html
- VPS OVHcloud : https://docs.ovhcloud.com/en/guides/bare-metal-cloud/virtual-private-servers/vps-faq

La documentation actuelle peut decrire une version plus recente : confronter
chaque procedure au code 2.4.0 de ce depot avant execution.

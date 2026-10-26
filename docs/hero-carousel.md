# Carrousel de la page d’accueil

Dans l’administration : **Configuration → Général → Accueil Louzir**.

- **Carrousel · Réglages et diapositive 1** : affichage global, lecture automatique, durée (3 à 30 secondes), activation de la première diapositive et champs existants.
- **Carrousel · Diapositive 2 — Homewear** et **Diapositive 3 — Coton** : activation, image, cadrage, textes et lien du bouton pour chaque diapositive.
- Images : JPEG, PNG ou WebP, maximum 5 Mo. Un paysage proche de 16:9, d’au moins 1600 px de large, est conseillé. Les trois images fournies mesurent 1672 × 941 px et pèsent 182 à 205 Ko en WebP.
- Cadrage horizontal ordinateur/mobile et vertical : 0 % correspond au bord gauche/haut, 50 % au centre, 100 % au bord droit/bas. Le voile sombre règle la lisibilité du texte.
- Les textes sont enregistrés pour la langue choisie. Les images, liens et réglages sont communs aux langues, et propres au canal choisi.
- Cliquer sur **Enregistrer la configuration**. Remplacer une image utilise le même champ de téléversement. Cocher **Supprimer** puis enregistrer rétablit le visuel fourni.
- Les boutons boutiques et réseaux reprennent les réglages existants. La première diapositive conserve les images et textes personnalisés déjà enregistrés.

Les flèches, la pagination, le clavier et le geste horizontal permettent de naviguer. Les flèches se trouvent au milieu des côtés de l’image. Le défilement continue au survol et après un clic sur les flèches. Il se suspend au focus clavier, pendant un geste tactile et quand l’onglet est masqué. Le bouton pause conserve le choix du visiteur. Le visuel coton inclut des chaussettes et reste remplaçable depuis la troisième diapositive. La préférence de réduction des animations désactive la lecture automatique au chargement. Une seule diapositive masque les commandes ; aucune diapositive active masque le hero. Sans JavaScript, la première diapositive reste visible.

## Vérification

Les tests PHP utilisent les transactions du projet. Exécuter avec une base de test configurée :

```powershell
php vendor/bin/pest packages/Louzir/Admin/tests/Feature/Configuration/HeroCarouselTest.php packages/Louzir/Admin/tests/Feature/Configuration/StitchHomeTest.php packages/Louzir/Shop/tests/Feature/HomeContentTest.php --compact
```

Contrôle navigateur sur un site local disposant des trois diapositives par défaut :

```powershell
$env:HERO_TEST_URL='http://127.0.0.1:8000'
node tests/browser/hero-carousel.cjs
```

Le script utilise Edge par défaut (modifiable avec PLAYWRIGHT_CHANNEL), vérifie navigation, lecture automatique, pause, tactile, largeur 320/390 px, réduction des animations, fonctionnement sans JavaScript et absence d’erreurs JavaScript. Les captures sont dans storage/framework/testing/hero-desktop.png et hero-mobile.png.

Les vérifications de cette intervention ont utilisé une base MariaDB isolée (port 33317, base hero_carousel_test), car la connexion MySQL du fichier .env était refusée. Le fichier .env et les données du site n’ont pas été modifiés.

## Affichage mobile et animations

Sur téléphone (jusqu’à 640 px), les photos du carrousel utilisent un format paysage 8:5 au-dessus du texte. Les flèches restent au centre de la photo et les commandes de lecture sous le contenu. La hauteur dépend du contenu, sans minimum de 760 px. Les réglages de cadrage mobile et vertical de chaque diapositive restent lus depuis l’administration. Les versions tablette et ordinateur conservent le texte sur l’image.

Une seule diapositive est visible pendant la navigation pour éviter les textes et images en double pendant un fondu. La photo reçoit un léger fondu de 350 ms. Au changement de diapositive, les textes apparaissent en cascade, sans superposition avec la précédente. Les sections sous la ligne de flottaison apparaissent une seule fois : léger déplacement de 8 px sur ordinateur avec pointeur précis, simple fondu sans déplacement sur téléphone et tablette. Le contenu déjà visible au chargement n’est pas masqué puis réaffiché. La préférence de réduction des animations désactive ces effets.

Le logo du bandeau et du menu mobile de l’administration possède des dimensions explicites : 90 × 28 px sur téléphone et 104 × 32 px au-delà. Le fichier reste modifiable dans Configurer → Général → Design. Un logo personnalisé est conservé lors du changement de thème clair/sombre.

Le scénario `node tests/browser/responsive-home-admin.cjs` vérifie les trois diapositives aux largeurs 320, 360, 390, 430, 640, 768, 1024 et 1440 px, le cadrage, les commandes, les animations et le logo de l’administration. Il utilise le compte administrateur de développement ou les variables DELIVERY_TEST_ADMIN_EMAIL et DELIVERY_TEST_ADMIN_PASSWORD. Les captures responsive-home-*.png et responsive-admin-*.png sont enregistrées dans storage/framework/testing.

## Animations inspirées de la référence

La page https://yalla-souk.tn/fr a servi de référence pour le rythme des apparitions, les ombres au survol et le retour visuel à la pression des boutons. Les textes, images, logo, polices et couleurs de Louzir sont conservés. Aucun fichier, script ni élément de marque du site de référence n’est intégré à la boutique. La capture de référence est conservée dans storage/framework/testing/yalla-souk-reference-desktop.png ; les captures Louzir sont landing-motion-desktop.png et landing-motion-mobile.png dans le même dossier.

Configurer → Général → Accueil Louzir → Animations de l’accueil permet d’activer les effets, de régler leur durée sur ordinateur (250 à 1000 ms, 700 par défaut) et le décalage entre éléments (0 à 120 ms, 60 par défaut). Sur téléphone et tablette, la durée est plafonnée à 350 ms et le décalage à 30 ms. Ces réglages suivent le canal, s’appliquent dans toutes les langues et sont validés côté serveur. Le défilement automatique du carrousel et le bandeau conservent leurs commandes indépendantes. Les effets ne déplacent pas les images du carrousel et ne modifient pas les hauteurs de mise en page.

Les collections et cartes boutiques gagnent une ombre progressive au survol avec souris. Les boutons utilisent une ombre douce et une pression à 97 % de leur taille. Les effets se terminent sans boucle, se suspendent pour le focus clavier et respectent la réduction des animations. Le scénario tests/browser/home-motion.cjs couvre ces comportements ; tests/browser/home-motion-admin.cjs vérifie les sauvegardes natives sur une base jetable avec MOTION_TEST_ALLOW_WRITES=1, puis restaure les valeurs initiales. HomeMotionTest couvre la sauvegarde, la publication dans les langues du site et la validation des limites sans dépendre des métadonnées du navigateur.

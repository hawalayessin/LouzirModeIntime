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

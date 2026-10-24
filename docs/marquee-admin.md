# Bandeau défilant — administration

Ouvrir **Configuration → Général → Accueil Louzir → Bandeau défilant**.

- **Afficher le bandeau défilant** : activer ou désactiver puis enregistrer la configuration.
- **Couleur de fond du bandeau**, **Couleur du texte du bandeau**, **Couleur des étoiles du bandeau** : choisir une couleur avec les sélecteurs natifs.
- La durée et les quatre messages restent configurables dans la même section.
- Les couleurs et l’affichage sont communs aux langues du canal choisi. Les messages restent propres à chaque langue.
- Désactiver le bandeau conserve ses couleurs et messages. Le réactiver les réutilise.

Les couleurs acceptées sont des codes hexadécimaux à six chiffres. La validation serveur reste active sans les métadonnées du navigateur. Une couleur sauvegardée invalide utilise la palette par défaut. Les interrupteurs de l’accueil participent à la validation du formulaire et transmettent une seule valeur explicite (0 ou 1). En cas d’erreur sur un autre champ, le formulaire conserve la valeur soumise.

Les métadonnées de validation du formulaire ne sont plus enregistrées dans les réglages. Les sauvegardes passent par le contrôleur et les événements existants, qui invalident le cache de la boutique.

## Vérification

Les tests PHP sont dans packages/Louzir/Admin/tests/Feature/Configuration/MarqueeSettingsTest.php.

Le scénario navigateur tests/browser/marquee-admin.cjs vérifie la sauvegarde complète, la désactivation et réactivation, la persistance des couleurs et leur application en français et anglais. Il restaure les réglages initiaux après le test. Il doit être exécuté sur une base isolée avec MARQUEE_TEST_ALLOW_WRITES=1. L’URL est configurable avec HERO_TEST_URL ; les identifiants avec MARQUEE_TEST_ADMIN_EMAIL et MARQUEE_TEST_ADMIN_PASSWORD (par défaut, compte du seeder de test).

Les captures sont enregistrées dans storage/framework/testing/marquee-admin.png et marquee-colors.png.

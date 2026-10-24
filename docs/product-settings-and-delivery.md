# Paramètres produit et livraison

La fiche produit conserve le nom interne de chaque attribut quand sa traduction est absente ou vide. Les traductions françaises, anglaises et arabes manquantes des attributs système ont été complétées, sans remplacer les libellés personnalisés. Les libellés restent modifiables dans **Catalogue → Attributs**. Les noms personnalisés des groupes sont conservés ; le groupe système Settings apparaît en français comme Paramètres.

Les modes **Livraison normale** et **Livraison express** sont disponibles dans **Configurer → Ventes → Modes de livraison** (`/admin/configuration/sales/carriers`). Chaque mode possède un titre, une description, un tarif, un mode de calcul et un interrupteur, avec les réglages par canal et les textes par langue. Le paiement lit ces réglages. Désactiver express le retire des nouveaux choix du client.

Le tarif initial de chaque mode est **8 dinars par commande**, soit **8,000 TND** avec les trois décimales de la boutique. Saisir `8` dans le champ tarif, et non `8000`. Les deux tarifs peuvent ensuite évoluer séparément. Le code historique `flatrate_flatrate` reste utilisé pour la livraison normale. La livraison gratuite reste disponible dans l’administration mais est désactivée initialement.

`StoreDeliveryOptionsSeeder` initialise uniquement les valeurs manquantes. Le relancer ne remplace pas les tarifs, titres ou interrupteurs déjà enregistrés. `ProductAttributeLabelsSeeder` complète uniquement les traductions système manquantes. Une sauvegarde avant intervention est conservée dans `storage/framework/testing/before-product-shipping-fixes.sql`.

Les tests PHP couvrent les traductions, la sauvegarde produit, les montants, les choix de livraison, les modifications administrateur, les valeurs invalides et la conservation des personnalisations. `tests/browser/admin-product-delivery.cjs` exerce les formulaires réels et le parcours de livraison sur la base isolée : il exige `DELIVERY_TEST_ALLOW_WRITES=1` et un produit de test. Il restaure les valeurs modifiées après le scénario. Le script de menu catégories vérifie aussi le contour de focus sur ordinateur et mobile.

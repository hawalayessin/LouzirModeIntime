# Typographie et boutons

La boutique et l'administration utilisent Manrope pour une présentation sobre et arrondie. L'arabe utilise Noto Sans Arabic lorsque Manrope ne possède pas le caractère demandé. Les couleurs du thème restent celles de la configuration existante.

## Réglages de la boutique

Dans **Configurer → Général → Accueil Louzir**, ouvrir les réglages de présentation :

- Police du texte : **Manrope**.
- Police des titres : **Identique au texte (police unique)**.

Les choix historiques restent disponibles. Avec « Identique au texte », changer la police du texte actualise aussi celle des titres. Après un déploiement sur un autre environnement, enregistrer ces deux valeurs dans son administration pour appliquer la présentation aux réglages déjà enregistrés.

## Styles communs

- Titres : graisse 600, interlignage aéré ; taille adaptée au contexte et à l'écran.
- Boutons principaux : rayon de 12 px, hauteur minimale de 48 px, libellés sans transformation automatique en capitales.
- Les boutons circulaires du carrousel et des cartes conservent leur forme et leur disposition.
- Le focus clavier et la préférence de réduction des animations sont pris en compte.

Les styles communs sont dans public/assets/styles/ui-typography.css. Les fichiers de police et leurs licences sont dans public/assets/fonts/ui. Le chargement commun passe par la vue core::components.ui-typography. Les styles et couleurs spécifiques à la boutique restent dans sa vue storefront-styles.

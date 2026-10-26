# E-mails clients et SMTP

Les réglages existants restent la référence : **Configurer → E-mails → Configurer** gère le SMTP, l’expéditeur, le contact et les messages personnalisés. **E-mails → Notifications** permet de désactiver séparément les nouvelles commandes, les confirmations administrateur et les messages de bienvenue à la newsletter.

## Configuration Gmail locale

Le compte d’envoi se définit dans l’administration, via `smtp.gmail.com:587` et STARTTLS obligatoire pour Gmail. Le champ mot de passe attend un mot de passe d’application Google. Il faut activer la validation en deux étapes puis créer ce mot de passe depuis <https://myaccount.google.com/apppasswords>. Il doit être saisi dans l’administration. Il n’est ni versionné ni réaffiché ; laisser le champ vide conserve la valeur existante. Les nouveaux secrets sont chiffrés avec la clé Laravel ; les anciennes valeurs restent lisibles pour compatibilité. Sauvegarder la clé de l’application avec la base est donc nécessaire pour restaurer les identifiants.

Le transport utilise d’abord les paramètres administrateur et conserve le repli `.env`. `MAIL_MAILER=bagisto-dynamic-smtp` doit rester actif pour que l’administration pilote l’envoi. SSL utilise TLS implicite, TLS impose STARTTLS et `none` convient au serveur de capture local. Les connexions ont un délai maximal de quinze secondes. Sans mot de passe pour un utilisateur SMTP configuré, le transport refuse immédiatement l’envoi avec une erreur explicite.

## Parcours et contenu

À la création, le client reçoit un accusé de réception indiquant que la commande attend sa confirmation. Le bouton administrateur **Confirmer la commande** fait passer une commande en attente à l’état de traitement sous verrou de ligne, puis émet `sales.order.confirm.after` après validation de la transaction. Les clics répétés ne provoquent pas de second envoi. Une facture ou une expédition conservent leurs propres notifications existantes.

Le message contient le nom du client, le numéro et la date, les articles et leurs variantes, les quantités, montants et frais enregistrés, le mode de livraison, le paiement et l’adresse. Les réglages fiscaux de présentation sont respectés. Un lien vers le compte n’est proposé qu’aux clients disposant d’un compte. Les invités reçoivent les mêmes informations utiles sans lien privé inutilisable. Le texte d’introduction accepte `:customer`, `:order` et `:store` et reste échappé ; il ne permet pas d’injecter du HTML. Les valeurs par défaut sont traduites en français, anglais et arabe.

La langue est attachée au courrier avant sa mise en file, à partir de la langue enregistrée dans la commande ; elle ne dépend pas de la langue de l’administrateur ou du worker. Le message de bienvenue conserve la langue choisie à l’inscription. Les e-mails sont mis en file après validation des transactions. Avec `QUEUE_CONNECTION=sync`, l’envoi reste synchrone ; en production, une connexion de file persistante et un worker supervisé permettent de gérer les délais et les reprises. Ne pas changer la connexion sans démarrer le worker correspondant.

La newsletter conserve sa gestion native dans **Marketing → Communications**. Le message d’accueil comporte un lien de désinscription utilisant un jeton aléatoire. Les campagnes utilisent leurs modèles et sujets administrateur et excluent les abonnements invités inactifs. La commande native `campaign:process` est planifiée quotidiennement par le module Marketing ; le planificateur Laravel doit être exécuté par l’hébergement. Aucun envoi de campagne réel n’a été lancé pendant la configuration.

## Vérification sans envoyer aux clients

Les suites `CustomerMailTest` et `CustomerOrderMailTest` vérifient SMTP, chiffrement, conservation du secret, validation, confirmation unique, langue, modèles, texte personnalisé, newsletter et destinataires actifs.

Pour la base jetable `hero_carousel_test`, démarrer `node tests/browser/smtp-capture.cjs`, puis lancer `tests/browser/mail-smoke.php` avec `MAIL_SMOKE_ALLOW_WRITES=1` et les variables de connexion de cette base. Le serveur écoute uniquement sur `127.0.0.1:2527`, accepte les courriers localement et ne les relaie pas. Le scénario transmet trois messages, rend neuf aperçus FR/EN/AR dans `storage/framework/testing`, puis annule toutes ses données de test. La réception SMTP locale ne confirme pas l’authentification Gmail ni la remise en boîte distante : un test réel reste nécessaire une fois le mot de passe enregistré.

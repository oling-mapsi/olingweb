# CRM : espace de travail et domaine dédié

Le logo PNG officiel fourni par OLING est conservé sans modification dans `public/crm/oling-logo.png`.

## Interface locale V2.1 — en attente de validation

Le Kanban affiche cinq étapes actives par défaut. Le filtre En cours / Clôturées / Toutes les étapes conserve les neuf statuts existants. Les colonnes ont une largeur minimale de 235 px sur ordinateur ; les cinq colonnes tiennent à 1280 et 1440 px. Sur petit écran, le défilement reste interne, avec accès aux colonnes et barre supérieure. Les détails des cartes s’ouvrent au clic ou au clavier. Actions et relances s’ouvre en liste, sauf choix explicite du Kanban.

Les KPI conservent exactement le calcul de `CrmDeadlines` livré avant cette évolution : tout le portefeuille, marchés et propositions clients, sous sept jours (retards inclus), au-delà et propositions envoyées/en discussion. Les statuts commerciaux `follow` / `preparing` priment sur la décision de veille ; une veille explicitement sélectionnée reste éligible en `qualify`. Les exclusions, dates manquantes et bornes Europe/Paris sont inchangées. Les listes restent disponibles au survol, au clic et au clavier. La description précédente de cette page (marchés uniquement, exclusion systématique de `reject`) était obsolète ; voir le README pour le périmètre actuel.

Recette, captures, limites et fichiers : [UX V2.1](UX-V2.1.md). Cette évolution est locale, sans fusion ni déploiement. Les informations d’exploitation ci-dessous décrivent l’installation existante, pas une intervention effectuée pour V2.1.

## Domaine et exploitation

DNS OVH : nouvel enregistrement A `crm.oling.fr` vers `54.37.230.240` ; aucune autre entrée modifiée. `scripts/crm/configure-domain.py` installe un vhost dédié après sauvegarde de release vérifiée hors serveur. Certificat Let's Encrypt distinct, utilisant le compte ACME existant ; renouvellement webroot avec rechargement Apache. La racine et `/admin` redirigent vers `/admin/crm`, y compris après connexion. Les règles Symfony d'authentification, CSRF et limitation des connexions existantes sont conservées. Aucun changement des cookies ou des permissions.

URL : `https://crm.oling.fr/`. L'URL historique `https://oling.fr/admin/crm` reste disponible. Les connecteurs conservent leurs endpoints historiques afin de ne pas interrompre les imports.

Déploiement : suivre `scripts/crm/deploy.py prepare`, copier uniquement l'archive chiffrée dans `../oling-crm-backups/<commit12>`, vérifier SHA256, puis créer le marqueur serveur `offsite-verified`. Intégrer la PR après CI et exécuter `activate`. Installer ensuite le domaine avec `sudo python3 scripts/crm/configure-domain.py /var/backups/oling/oling-crm-<commit12>` depuis la release. Aucune migration supplémentaire n'est nécessaire.

Retour arrière applicatif : basculer atomiquement `/var/www/oling-current` sur la release précédente sous verrou de déploiement, recharger Apache ; conserver les données et les tables. Retour arrière domaine : désactiver uniquement `crm.oling.fr.conf` avec `a2dissite`, vérifier `apache2ctl configtest`, recharger Apache, puis retirer uniquement le nouvel enregistrement DNS CRM si nécessaire. Le domaine principal est inchangé. La configuration installée est conservée dans la sauvegarde serveur ; les clés TLS restent exclusivement sur le serveur.

Version locale complète : `olingw-crm`, lancement `./scripts/crm/local-start.sh`, http://127.0.0.1:8092/admin/crm. SQLite fictive, synchronisations et tâches désactivées. Aucun secret dans Git.

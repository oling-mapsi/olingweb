# CRM : espace de travail et domaine dédié

Le logo PNG officiel fourni par OLING est conservé sans modification dans `public/crm/oling-logo.png`.

Le Kanban affiche les neuf colonnes sur une largeur d'au moins 1200 px. Les cartes défilent verticalement dans chaque colonne. « Agrandir les cartes » ouvre un mode plus large ; une barre horizontale située au-dessus du tableau et les boutons de colonnes permettent de rejoindre directement chaque étape. Les mêmes accès sont proposés sur petit écran. Le détail reste disponible au survol, au clic et au clavier ; le déplacement reste versionné et protégé par CSRF.

Les KPI sont calculés sur tout le portefeuille, indépendamment du filtre affiché. Sont retenus les marchés ayant une décision explicite `follow`, ou un statut commercial `follow` / `preparing` choisi dans le portefeuille, y compris lorsque la décision de veille est encore `review`. Une décision explicite `reject` reste exclue jusqu'à sa modification. Sont exclus les doublons fusionnés, imports à réviser, décisions non retenues, clients privés, statuts `no_go`, `won`, `lost`, `archived`, et réponses déjà déposées (`sent`, `discussion`). Le bloc affiche les échéances sous sept jours, dépassées, les trois prochaines dates ordonnées et le nombre de dates manquantes. Les heures sont affichées en Europe/Paris. Le déplacement de carte ou le changement de décision recalcule immédiatement le bloc.

## Domaine et exploitation

DNS OVH : nouvel enregistrement A `crm.oling.fr` vers `54.37.230.240` ; aucune autre entrée modifiée. `scripts/crm/configure-domain.py` installe un vhost dédié après sauvegarde de release vérifiée hors serveur. Certificat Let's Encrypt distinct, utilisant le compte ACME existant ; renouvellement webroot avec rechargement Apache. La racine et `/admin` redirigent vers `/admin/crm`, y compris après connexion. Les règles Symfony d'authentification, CSRF et limitation des connexions existantes sont conservées. Aucun changement des cookies ou des permissions.

URL : `https://crm.oling.fr/`. L'URL historique `https://oling.fr/admin/crm` reste disponible. Les connecteurs conservent leurs endpoints historiques afin de ne pas interrompre les imports.

Déploiement : suivre `scripts/crm/deploy.py prepare`, copier uniquement l'archive chiffrée dans `../oling-crm-backups/<commit12>`, vérifier SHA256, puis créer le marqueur serveur `offsite-verified`. Intégrer la PR après CI et exécuter `activate`. Installer ensuite le domaine avec `sudo python3 scripts/crm/configure-domain.py /var/backups/oling/oling-crm-<commit12>` depuis la release. Aucune migration supplémentaire n'est nécessaire.

Retour arrière applicatif : basculer atomiquement `/var/www/oling-current` sur la release précédente sous verrou de déploiement, recharger Apache ; conserver les données et les tables. Retour arrière domaine : désactiver uniquement `crm.oling.fr.conf` avec `a2dissite`, vérifier `apache2ctl configtest`, recharger Apache, puis retirer uniquement le nouvel enregistrement DNS CRM si nécessaire. Le domaine principal est inchangé. La configuration installée est conservée dans la sauvegarde serveur ; les clés TLS restent exclusivement sur le serveur.

Version locale complète : `olingw-crm`, lancement `./scripts/crm/local-start.sh`, http://127.0.0.1:8092/admin/crm. SQLite fictive, synchronisations et tâches désactivées. Aucun secret dans Git.

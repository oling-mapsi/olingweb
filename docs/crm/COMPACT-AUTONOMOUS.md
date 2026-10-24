# CRM compact et accès autonome

Les filtres sont dans l’en-tête ; les KPI sont alignés avec le titre sur ordinateur. À 1440 et 1280 × 900, le Kanban commence à 286 px et occupe 599 px, contre environ 360 px auparavant. En dessous de 1100 px, les KPI passent sous le titre, les colonnes conservent une largeur lisible et leurs raccourcis restent disponibles. Aucun calcul métier modifié.

## Accès

https://crm.oling.fr ouvre le portefeuille, avec sa propre page `/crm/login`, le firewall `crm` et le cookie de session `OLINGCRMSESSID`. Les identifiants et rôles proviennent du même fournisseur `App\Entity\User` que l’administration historique ; aucun compte ni mot de passe n’est créé ou modifié. Les sessions et déconnexions sont indépendantes. Le rôle administrateur reste obligatoire. CSRF à la connexion/déconnexion et limitation à cinq tentatives par quinze minutes.

Le sous-domaine refuse les autres pages de l’application. `/login` redirige vers la mire CRM et `/admin` vers le portefeuille. Le domaine OLING conserve son authentification habituelle. Il s’agit d’une séparation d’interface et de session, avec application et base des accès communes.

En local : `scripts/crm/local-start.sh`, puis http://crm.localhost:8092. Données fictives uniquement ; identifiants dans `var/crm-local-login.txt`, hors Git.

## Livraison

Paquet produit depuis le commit fusionné uniquement. L’overlay ajoute le contrôleur de connexion, le subscriber et le fragment `scripts/crm/crm-firewall.yaml`. `patch-security.py` insère ce fragment dans la configuration de la release préparée en conservant intégralement la configuration serveur du firewall principal. Ne pas archiver le fichier local `config/packages/security.yaml` dans le paquet.

La bascule recharge Apache puis, si présent et actif, le conteneur PHP-FPM `oling-php84` par USR2 : ceci renouvelle ses caches opcode/realpath sans lesquels il peut continuer à servir les anciens templates. Cette remise à jour s’applique aussi au rollback. Aucune migration nouvelle, dépendance, modification de connecteur ou tâche planifiée.

Sauvegarde chiffrée, restauration SQL de contrôle et copies hors serveur vérifiées avant activation. Conserver la release précédente ; rollback par bascule de lien et rechargement PHP, sans restauration de données commerciales.

## Recette

47 tests CRM, 528 assertions : sessions isolées, réutilisation des comptes, CSRF, connexion et déconnexion, accès refusés, cinq colonnes et filtres. Contrôles PHP, Twig, conteneur, JavaScript et Python. Connexion/déconnexion et persistance de session vérifiées dans Chrome sur la base synthétique. Captures finales : [1440](compact-autonomous/kanban-1440.png), [1280](compact-autonomous/kanban-1280.png), [768](compact-autonomous/kanban-768.png), [375](compact-autonomous/kanban-375.png), [connexion](compact-autonomous/login.png). Aucun débordement global horizontal aux quatre formats.

Les noms internes `admin_crm_login` et `admin_crm_logout` excluent les post-traitements HTML publics présents uniquement sur le serveur, sans changer les URL `/crm/login` et `/crm/logout` ni partager le firewall ou la session de l’administration. Le fragment de sécurité sait remplacer exactement sa version initiale ; toute autre définition CRM reste refusée.

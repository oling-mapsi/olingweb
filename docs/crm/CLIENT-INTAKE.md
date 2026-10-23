# Demandes clients : formulaires uniquement

Politique du 23 octobre 2026 : les nouvelles demandes automatiques viennent exclusivement des formulaires d'oling.fr et de mapsi.fr, via leurs flux directs existants. La saisie manuelle reste disponible pour reprendre les dossiers déjà en cours. La veille des marchés reste indépendante.

`client_intake: forms_only` (défaut du code) interdit toute lecture Microsoft Graph, même si `email_enabled` était accidentellement réactivé. En production, ce dernier indicateur est également à `false`. Le jeton Microsoft est conservé uniquement sur le serveur mais n'est plus utilisé. Aucun email n'est supprimé dans Outlook. Le timer reste actif pour les reprises des formulaires ; la veille reste active. Le bouton de synchronisation Outlook est désactivé.

## Remise à zéro ciblée

`scripts/crm/client-intake-policy.py` désactive l'alimentation email et fige les identifiants des demandes privées visibles à cet instant dans `/var/lib/oling-crm/client-reset-20261023.json`. Il exclut les marchés publics, les fiches de veille, les anciens imports en révision et les fiches déjà fusionnées. Les booléens JSON MySQL sont comparés explicitement comme chaînes `true` / `false`, sans conversion numérique implicite. La configuration antérieure reste dans un dossier serveur root 0700, jamais copiée en clair hors serveur.

Après sauvegarde chiffrée vérifiée hors serveur, intégration et déploiement, exécuter comme root :

```sh
APP_ENV=prod APP_DEBUG=0 php /var/www/oling-current/scripts/crm/reset-visible-clients.php /var/backups/oling/oling-crm-<commit12>
```

Le script supprime seulement les identifiants figés, dans une transaction. Une vérification du périmètre protège les marchés publics et les imports à réviser ; l'empreinte de tous les marchés publics et de leur historique doit rester identique avant/après. Les événements, sources et identités des clients supprimés sont retirés. Des empreintes de déduplication, sans contenu commercial, empêchent un rejeu d'email ou de formulaire de recréer une fiche supprimée. Les nouvelles demandes reçues après la sélection sont conservées.

Les anciens imports en révision restent isolés dans leur vue historique ; aucun import email ne rejoint les vues commerciales en mode `forms_only`. La réinitialisation est refusée si elle a déjà eu lieu, pour protéger les formulaires plus récents.

Retour arrière : la release précédente peut être réactivée, mais conserver `email_enabled: false`. Toute restauration de fiches depuis la sauvegarde chiffrée demande une décision explicite ; aucune restauration globale automatique de base. Le jeton OAuth et la clé de sauvegarde restent sur le serveur.

Vérification : tests sur base synthétique de la désactivation Graph, des flux OLING/MAPSI, de la déduplication après suppression, de la conservation des marchés et des formulaires plus récents. En production, recette transactionnelle annulée, contrôle du nombre supprimé, des formulaires et du timer, puis lecture du portefeuille vide côté clients.

# CRM V2.1 — Corrections finales et recette

24 octobre 2026 · `feat/crm-ux-v2-1` · Base UX `cbf1d152` · **GO pour préparation d’intégration.**

## Corrections approuvées

- Les filtres vérifient chaque date pertinente indépendamment : relance ou dépôt/remise restant à effectuer. Le tri conserve la plus ancienne de ces dates. Les dépôts déjà effectués restent exclus ; aucune date n’est créée ou modifiée par la navigation.
- « Aujourd’hui » couvre la journée de Paris. « En retard » couvre les dates passées ; « 7 prochains jours » couvre uniquement les dates à venir. Une affaire avec deux dates peut donc appartenir à plusieurs filtres. Le cas dépôt hier + relance aujourd’hui fonctionne, ainsi que le cas inverse.
- Mobile : marges, KPI et commandes compactés ; filtres sur une ligne lorsque possible. Aucune taille de caractères réduite ni fonctionnalité essentielle retirée.
- KPI : mention permanente « Retards inclus » et explication dans la mini-liste. Calculs et périmètre marchés publics + clients inchangés (`CrmDeadlines.php` et ses tests métier inchangés).
- Un seul bouton Enregistrer, déplacé en tête du formulaire existant dans une barre persistante. Pas d’autosauvegarde, de formulaire supplémentaire ni de changement CSRF/version. Le focus des champs reste dégagé sous la barre.

## Vérifications

**46 tests CRM / 486 assertions réussis.** Contrôles PHP (contrôleur et test), Twig (8 templates), JavaScript, conteneur Symfony et `git diff --check` réussis. Les 15 nouveaux cas temporels couvrent les deux dates croisées, passé/présent/futur, dépôts effectués, absence de dates, minuit Paris et la borne J+7 avec changement d’heure.

Recette Chrome sur SQLite fictive uniquement :

- Modification de responsable et de remise, sauvegarde puis contrôle des valeurs persistées ; restauration des valeurs de démonstration avec Enregistrer au clavier.
- Actions/Aujourd’hui : présence du client avec remise hier et relance aujourd’hui, libellés et dates distincts.
- Passage du marché de En préparation à Offre envoyée : retrait immédiat du filtre 7 prochains jours lorsque son dépôt n’est plus à effectuer ; fiche retrouvée dans le portefeuille, puis remise en préparation.
- Navigation Tab/Entrée/Esc, focus visible et non recouvert pendant la saisie ; ouverture/fermeture du KPI au clavier et accès mobile à la colonne À suivre.
- Les 15 fiches fictives restent présentes (14 actives, une Sans suite). Les valeurs modifiées pour cette recette ont été restaurées ; l’historique local garde les essais.

| Capture finale | Résultat visuel |
| --- | --- |
| [1440 px](ux-v2-1/final/board-1440.png) | Cinq colonnes de 267 px ; aucun débordement de page. |
| [1280 px](ux-v2-1/final/board-1280.png) | Cinq colonnes de 235 px ; textes lisibles, aucun débordement de page. |
| [768 px](ux-v2-1/final/board-768.png) | Défilement interne du Kanban et raccourcis de colonnes accessibles. |
| [375 px](ux-v2-1/final/board-375.png) | Début du Kanban à 774 px au lieu d’environ 998 px : gain de 224 px (22 %). Page de 375 px sans débordement. |

Preuves complémentaires : [dates croisées](ux-v2-1/final/today-two-dates-1440.png), [enregistrement confirmé](ux-v2-1/final/edit-1440.png), [barre persistante mobile](ux-v2-1/final/save-sticky-375.png), [explication KPI](ux-v2-1/final/kpi-375.png), [colonne mobile](ux-v2-1/final/kanban-focus-375.png), [Actions mobile](ux-v2-1/final/actions-375.png).

## Réserves et périmètre

Aucune anomalie bloquante identifiée. Sur 375 px, la première carte nécessite encore un court défilement vertical ; le tableau Actions conserve son défilement horizontal interne. Le mobile a été vérifié dans Chrome à dimensions imposées, sans appareil tactile physique, lecteur d’écran ni recette Safari/Firefox.

Aucun merge, push, déploiement, accès serveur ou données réelles. Connecteurs et configurations inchangés. Les modifications préexistantes de cinq fichiers `vendor` sont conservées hors commit.

Version locale : `/Users/florestanrouet/myweb/olingw-crm` ; lancement `scripts/crm/local-start.sh`, puis http://127.0.0.1:8092/admin/crm. Le serveur local existant a été réutilisé.

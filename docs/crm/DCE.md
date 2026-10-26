# Confirmation du DCE — 26 octobre 2026

Les cartes Kanban des marchés publics (type public ou origine veille) affichent un bouton **DCE** à côté de Détails. Cercle vide : à obtenir ; point vert coché : obtenu. Le survol explicite l'état ; un clic, Entrée ou Espace confirme ou annule. Le libellé accessible et `aria-pressed` distinguent les états sans dépendre uniquement de la couleur.

Le choix est manuel, historisé et conservé dans le JSON de la fiche, avec une valeur par défaut `false` pour les fiches existantes : aucune migration. Les imports et l'édition ordinaire ne peuvent pas le modifier. L'endpoint POST dédié conserve la protection administrateur, CSRF et version ; refus sur une fiche client. La confirmation ne modifie ni le statut ni les dates ni les décisions de veille. Les erreurs sont visibles, sans confirmation optimiste, et les écritures concurrentes du Kanban sont bloquées pendant la requête.

Recette fictive : 56 tests CRM, 759 assertions. PHP, Twig, container Symfony et JavaScript valides. Activation et annulation au clavier, rechargement, déplacement de carte puis maintien du DCE ; bureau 1280 px et mobile 375 px sans débordement de page, bouton sur la ligne existante (40 px). Aucune erreur JavaScript. Captures : [bureau](dce/desktop.png), [mobile](dce/mobile.png).

La recette serveur `app:crm:check` couvre également la confirmation et sa conservation après enrichissement, dans une transaction annulée. Déploiement via l'overlay CRM habituel, après sauvegarde avec restauration SQL vérifiée, copie chiffrée locale et dump SQL privé Dropbox. Retour arrière par la release précédente, sans restauration de base : la propriété JSON reste conservée.

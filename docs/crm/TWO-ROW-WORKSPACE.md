# Espace commercial compact — 24 octobre 2026

Sur ordinateur : logo, Portefeuille (Tous / B2B / B2G), Actions et relances, recherche, filtre et déconnexion sur une ligne. La seconde regroupe les vues, le nombre affiché, les trois KPI, les zones Perdue/Gagnée avec compteurs, Export et Nouvelle affaire.

Le Kanban utilise la hauteur restante jusqu'au pied de page. Les filtres et règles métier restent inchangés. Les zones de clôture utilisent le même POST avec CSRF et version ; leurs liens ouvrent les affaires clôturées avec les filtres courants. Au clavier/tactile, le sélecteur dans Détails reste disponible. Sur petit écran, les commandes se répartissent et les raccourcis de colonnes défilent horizontalement.

« Connexion et alimentation » ouvre au survol, clic ou clavier les seuls canaux activés : agent marchés, formulaires oling.fr et mapsi.fr. Les dates distinguent connexion et réception ; un contrôle de reprise ancien ou en erreur ne reçoit pas un état opérationnel. En local, aucune alimentation active. Outlook n'est pas présenté ni activé.

## Recette locale

Données exclusivement fictives. 68 tests CRM, 698 assertions. PHP, Twig, container Symfony et JavaScript vérifiés. Menu B2B/B2G : 12 clients et 2 marchés attendus ; clôture perdue puis gagnée et retour en qualification vérifiés dans les vues correspondantes. Navigation clavier du menu, des popovers et du statut vérifiée. Déplacement entre colonnes observé avec souris native ; clôtures vérifiées par le sélecteur et les tests HTTP (CSRF, conflit de version, compteurs).

| Écran | Début du Kanban | Hauteur utile | Débordement de page |
|---|---:|---:|---|
| 1440 × 900 | 115 px | 743 px | Aucun |
| 1280 × 900 | 115 px | 743 px | Aucun |
| 768 × 1024 | 269 px | 713 px | Aucun |
| 375 × 812 | 344 px | 427 px | Aucun |

Captures : [1440](two-row-workspace/1440.png), [1280](two-row-workspace/1280.png), [768](two-row-workspace/768.png), [375](two-row-workspace/375.png).

## Livraison

Overlay CRM uniquement, sans migration ni configuration de connecteur. L'application IA désormais présente partage la configuration de sécurité : le déploiement vérifie son manifeste actif si ce fichier a changé depuis le dernier manifeste CRM. Tout écart non couvert reste bloquant. Le patch CRM laisse la configuration combinée IA/CRM strictement identique. Sauvegarde, preuve de restauration SQL, copie chiffrée locale et SQL privé Dropbox avant activation ; release précédente conservée.

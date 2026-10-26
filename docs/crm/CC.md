# Cadrage B2B — bouton CC

Le bouton **CC** des cartes B2B indique qu'un cahier des charges ou un échange de cadrage / avant-vente a été obtenu. Cercle vide : à obtenir ; point bleu coché : obtenu. Le survol précise le sens du bouton. Clic, Entrée et Espace confirment ou annulent. Les marchés publics conservent leur bouton DCE vert.

La confirmation manuelle `cc_received` est indépendante de `dce_received`, historisée et stockée dans le JSON existant (défaut false, aucune migration). Imports et édition ordinaire la conservent. Le POST `/admin/crm/{id}/cc` vérifie rôle administrateur, CSRF, version et périmètre B2B ; il ne modifie ni statut ni dates ni prochaine action. DCE et CC partagent le traitement navigateur des erreurs et de l'enregistrement, sans mise à jour optimiste.

Recette : 57 tests CRM / 800 assertions, PHP/Twig/container/JS et recette transactionnelle valides. Clic, annulation clavier, rechargement, déplacement et conservation du CC vérifiés ; DCE vert toujours fonctionnel. Bureau 1280 px et mobile 375 px sans débordement, boutons sur la ligne Détails existante. [Bureau](cc/desktop.png) · [Mobile](cc/mobile.png).

Déploiement avec scripts/crm/deploy.py, phases prepare puis activate, depuis le commit intégré. **Exclure du paquet** `src/Service/Crm/ContactReceiptSubscriber.php` et `src/Service/Crm/FormQueue.php` : adaptations des formulaires présentes sur la release active depuis le 25 octobre, à conserver et vérifier par empreinte. Ne pas déployer le workspace complet. Sauvegarde chiffrée locale sans clé, dump SQL privé Dropbox avec relecture intégrale et preuve de restauration avant bascule. Retour arrière vers la release précédente sans restauration de base : les propriétés JSON restent conservées.

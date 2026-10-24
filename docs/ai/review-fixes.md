# Corrections de revue — état figé pour relecture

Aucun lancement réel de Codex, serveur local ou accès production effectué. Le présent état corrige les quatre constats reçus ; il ne vaut pas autorisation d’activation.

| Constat | Correction locale | Test et portée |
|---|---|---|
| P1 — héritage du token relais et de l’environnement | `childEnvironment` transmet une allowlist minimale ; PATH contrôlé, absence de tokens/API keys/variables de chargement. Configuration transitoire des outils et sandbox explicite ; modèle, politique d’approbation et sandbox renvoyés contrôlés. Aucun accès direct aux fichiers d’authentification. | Le pair enfant reçoit un environnement empoisonné fictif : secrets absents. Les tests inspectent les paramètres transmis et refusent une sandbox élargie ou un MCP actif. Enforcement OS et compatibilité CLI réelle restent non validés. |
| P1 — continuation après refus d’approbation | `turn/interrupt` émis avant `decline`. Résultat jamais réussi après refus. Attente d’acquittement et de fin ; processus arrêté si nouvel outil ou timeout. | Pair synthétique qui continue après refus, succès tardif, nouvel outil et absence d’acquittement. Toutes ces branches passent. Aucun test réel de refus Codex effectué. |
| P2 — bail expiré renvoyant encore running | L’expiration est persistée sur heartbeat et avant rejet d’une livraison tardive, sans nouveau claim. État `uncertain`, mission interrompue ; côté relais, arrêt et journal `needs_reconciliation`. Un 409 de livraison n’est plus rejoué en boucle. | Suspension simulée >45 s, heartbeat sans autre claim, livraison tardive, relecture du journal sans nouvelle exécution. HTTP activé seulement avec credential fictif en mémoire. |
| P2 — signal perdu pendant initialize | Vérifications avant initialisation, après initialisation, avant claim et avant exécution. Annulation de la réservation sans inférence lorsqu’un signal arrive entre ces étapes. | Signal déjà annulé et annulation dans initialize : zéro claim et zéro exécution. |

## Vérifications complémentaires ajoutées

- Deux administrateurs par HTTP : accès croisé aux missions/assets et annulation d’une mission étrangère refusés.
- Endpoint relais activé avec une valeur fictive : mauvais credential, HTTP et usurpation de propriétaire refusés ; livraison rejouable, expiration et conflit vérifiés.
- Deux processus et deux connexions SQLite réservent simultanément parmi deux missions : une seule réservation active. L’upsert du statut exécutant précède les lectures pour éviter un snapshot antérieur au verrou. MySQL/MariaDB reste à tester en environnement dédié.
- Cookie AI final sans domaine, Secure, HttpOnly et SameSite=Lax ; subscriber exécuté après celui des sessions Symfony.
- Prompt/résultat hostiles contenant du HTML affichés échappés ; rapport d’agent explicitement non certifié. Un texte « tests passed » ne produit pas une preuve métier vérifiée.

## Deuxième cycle et état final

Les conclusions initiales ci-dessus sont complétées par [le deuxième cycle correctif](review-cycle-2.md). Le confinement de lecture du runtime reste non certifié : lancement réel inconditionnellement bloqué, y compris sonde sans outils. L'arrêt attend désormais la sortie du groupe POSIX dédié avec escalade bornée, testé sur parent/enfant factices ignorant SIGTERM et groupe témoin.

Derniers résultats après ce cycle : **AI 15 tests / 123 assertions** ; **Node 26 tests réussis** ; **CRM relancé séparément, 1 test / 42 assertions**. Lints relancés et réussis. Voir `test-report.md`.

Le P1 de confinement ne peut pas être déclaré résolu sur le runtime réel ; exposition neutralisée par le refus applicatif, arbitrage requis avant toute réouverture. Les deux actions réelles restent en attente d'autorisation. Aucun service, credential réel ou publication.

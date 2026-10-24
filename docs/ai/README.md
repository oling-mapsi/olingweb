# OLING IA — V1 simple

Version de référence actuelle. Le site conserve missions, prompts versionnés, résultats privés, revues et preuves. L’interlocuteur pilote les tâches Codex natives par les capacités de sa plateforme. Symfony ne lance aucun modèle ni processus Codex. Growth reste indépendant.

Les quatre vues sont Aujourd’hui, Missions, Assets et Prompts. Même fournisseur `app_user` que OLING/CRM, administrateurs seulement, session `OLINGAISESSID` propre à l’hôte sans cookie `.oling.fr`. Une demande nouvellement créée attend sa prise en charge ; aucun bouton ne prétend lancer une tâche.

## Suivi natif

`AiNativeRegistry` gère demande → association d’une tâche externe → résultat déclaré → revue → deux corrections au plus → validation humaine et clôture. Le propriétaire humain et l’exécutant externe sont distincts. Un seul exécutant par mission ; événements idempotents, versions attendues, verrou transactionnel, liens et limites de taille validés. Les résultats d’exécutants restent explicitement non vérifiés indépendamment. Annuler une mission active demande un arrêt ; seul l’événement externe d’arrêt l’acquitte.

Le contrat de huit opérations est [documenté ici](mcp-contract.md). Le point d’entrée `/api/ai/mcp` retourne **503** tant que l’adaptateur OAuth n’est pas raccordé. Il ne réutilise ni les cookies du site ni les droits du connecteur CRM. **Aucun plugin OLING IA connecté et aucun aller-retour vivant avec cette conversation ne sont validés.** La recette est une fixture, pas une inférence.

## Publication

Voir [plan et rollback](deployment-plan.md), [recette exacte](test-report.md) et [manifeste](manifest.json). Deux migrations, dans cet ordre : `Version20261024180000` puis `Version20261024220000`. Elles créent dix tables `ai_*` sans modifier les données OLING/CRM. Les résultats sont privés, hors document root, conservés entre releases.

## Travaux historiques exclus

Les fichiers `app-server.mjs`, `relay.mjs`, `probe.mjs`, leurs tests et les rapports `review-*`, `probe-result.json`, `local-qualification.md` documentent une expérimentation antérieure. Ils ne constituent pas une fonctionnalité active de cette V1. Le client réel demeure verrouillé avant spawn ; `/api/ai-relay` refuse toujours 403, même avec les variables historiques activées. Aucun service persistant, token ou clé OpenAI à créer pour publier l’interface.

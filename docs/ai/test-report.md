# Recette V1 simple — 24 octobre 2026

Base `876770b48e09e135b890ff617bc1a81b6ee4c730` (PR29 et ses deux zones Kanban conservées), branche `codex/ai-control-local`. PHP8.2.28 local ; dépendances CRM réutilisées en lecture seule, configuration et données synthétiques. La fraîcheur distante a été revérifiée par fetch après autorisation de publication.

| Suite indépendante | Résultat final |
|---|---|
| AI `phpunit -c scripts/ai/phpunit.xml` | **22 tests,165 assertions, PASS** |
| CRM `phpunit -c scripts/ai/phpunit-crm.xml` (13 fichiers) | **53 tests,643 assertions, PASS** |
| HTTP réel sur127.0.0.1:8094 | **23 contrôles,0 échec**, `local-http-result.json` |
| Chrome desktop et390px | Cycle fictif revu/corrigé puis clôturé dans l’interface, affichage lisible, déconnexion ; captures `v1-simple-*` |
| PHP, conteneur, Twig, YAML, Python et diff | Lints exécutés sur sources du lot ; preuves dans le manifeste |

Commandes PHP avec `AI_TEST_VENDOR=/Users/florestanrouet/myweb/olingw-crm/vendor SYMFONY_DEPRECATIONS_HELPER=disabled`, binaire PHPUnit `/Users/florestanrouet/myweb/olingw-crm/vendor/phpunit/phpunit/phpunit`. Les totaux AI et CRM ne sont pas fusionnés.

La suite AI comprend AiAccessTest1, AiConcurrencyTest1, AiRelayAccessTest2, AiWorkflowTest11, AiNativeRegistryTest6, AiTrackingHttpTest1. Les tests workflow/relais hérités sont des simulations de stockage ; aucun appel Codex. Le test HTTP relais prouve désormais que même une configuration historique factice activée est refusée403. Les26 tests Node du cycle2 demeurent des résultats historiques non relancés dans ce lot ; sources Node inchangées, client réel toujours verrouillé.

## Couverture V1 simple

Création, identité propriétaire, tâche native factice, modèle/effort explicites, événements idempotents, rejet du rejeu altéré et de version périmée, exécutant unique, résultat structuré et liens sûrs, revue/correction/validation, plafond de deux corrections, annulation avec acquittement externe. Deux processus SQLite avec connexions indépendantes se disputent une association : un seul gagne. Concurrence MySQL non qualifiée par ce test.

HTTP avec faux adaptateur OAuth exclusivement dans le conteneur de test : scope et propriétaire, HTTPS/origine, notifications sans mutation, limites, non-admin, endpoint indisponible par défaut. Ce n’est pas une validation OAuth réelle. Tests UI/session/CSRF et accès croisés entre deux administrateurs ; secrets synthétiques, HTML agressif et déclaration de succès non vérifiée. Les valeurs cookies/CSRF restent en mémoire, jamais dans les rapports.

`v1-native-fixture-result.json` documente le cycle local, puis la clôture par UI. Aucun modèle exécuté ; aucun identifiant factice présenté comme tâche réelle. Les preuves déclarées sont distinguées des assertions exécutées. Le navigateur et le serveur de recette ont été fermés après vérification.

## Limites

Plugin vivant/OAuth/consentement non raccordés. Aucune mission native réelle aller-retour. Publication de l’interface et raccordement sont deux jalons distincts. Aucun nouveau compte ou credential. Les tests HTTP authentifiés de production nécessitent une connexion utilisateur existante ; aucune session privée n’est extraite. Les anciennes qualifications de sonde et de relais sont historiques, remplacées fonctionnellement par la V1 simple.

# /crm — after

Date de capture: 2026-08-18
Source: page live `https://oling.fr/crm?phase2a3=live3`

- URL: `https://oling.fr/crm`
- title before -> after:
  - `AMOA CRM | Parcours client, donnees et adoption metier | OLING`
  - `AMOA CRM : cadrage, choix et déploiement de votre CRM | OLING`
- meta description before -> after:
  - `AMOA CRM pour directions commerciales, marketing et SI : cadrage cible, modele de donnees, gouvernance commerciale, recette, adoption et integration SI.`
  - `Cabinet AMOA CRM indépendant : cadrage métier, choix de solution, pilotage intégrateur, données, migration, recette et conduite du changement.`
- H1 before -> after:
  - `AMOA CRM : structurer le parcours client et la performance commerciale`
  - `AMOA CRM : cadrer, choisir et reussir votre projet CRM`
- H2 before -> after:
  - before: `Ce que nous cadrons` / `Livrables AMOA` / `Maillage` / `Cas clients et realisations`
  - after: `Pourquoi OLING intervient sur un projet CRM` / `Ce que couvre une mission AMOA CRM` / `Quand lancer ou refondre un CRM` / `Livrables utiles pour cadrer, choisir et deployer` / `Situations ou OLING apporte le plus de valeur` / `Equipes et organisations concernees` / `Explorer les sujets lies a un projet CRM`
- blocs ajoutés:
  - positionnement explicite `cabinet AMOA CRM independant`
  - phases CRM couvertes: cadrage, choix, pilotage integrateur, migration, recette, conduite du changement
  - livrables visibles: roadmap CRM, backlog metier, matrice de scoring, plan de reprise des donnees, strategie de recette
  - contextes d'usage: CRM peu adopte, refonte, migration sensible, projet transverse marketing / commerce / service client / SI
- preuves utilisées:
  - bloc live `Cas clients et realisations`
  - references vers `/projets`
  - equipe via `/a-propos/team`
- liens ajoutés:
  - `/amoa-si`
  - `/business-apps/erp`
  - `/gmao`
  - `/si-finance`
  - `/projets`
  - `/a-propos/team`
  - `/ressources`
- schema:
  - `Service` conserve
  - `serviceType` confirme `AMOA CRM`
  - `description` alignee sur la meta visible
- requêtes ciblées:
  - `EXISTING_GSC_QUERY`: `amoa crm`
  - `TARGET_QUERY`: `conseil crm`, `cabinet amoa crm`, `choix crm`, `cadrage crm`, `refonte crm`, `migration crm`, `projet crm`, `deploiement crm`
- risques:
  - `POSSIBLE`: overlap semantique avec `/business-apps/erp` et `/amoa-si` si les exports `page x query` restent incomplets
  - `POSSIBLE`: le bloc historique issu de la base conserve encore un lien vers `/erp-progiciel`
  - `CONFIRMED`: aucune 301 nouvelle
- hypothèses à mesurer:
  - meilleure pertinence sur `amoa crm`
  - meilleure comprehension de l'offre CRM par les moteurs de reponse
  - meilleur maillage contextuel vers les actifs AMOA / ERP / GMAO / SI Finance

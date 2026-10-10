# Audit agent IA commercial OLING - 2026-10-08

Perimetre: audit uniquement. Aucune modification du site.

Sources controlees: code local `src/Service/Chat/*`, `data/i18n/ai_consultant.fr.json`, `public/llms.txt`, `robots.txt`, sitemaps publics FR/EN/ES, pages OLING indexees, pages MAPSI publiques, LinkedIn OLING, Pappers/Kompass/CINOV quand utile.

## A. Executive Summary

1. L'agent actuel est construit comme assistant expert/FAQ augmente, pas encore comme consultant avant-vente.
2. Le prompt impose "repondre d'abord", "ne pas questionner inutilement", "contact seulement si demande ou projet concret": c'est sain, mais trop defensif pour convertir.
3. Le fallback heuristique local produit des reponses souvent exactes mais pauvres commercialement: peu de reformulation, peu de preuves, peu de CTA contextualises.
4. Le banc integre ne teste que 20 questions, sans score /100, sans objections, sans parcours multi-tours, sans persona.
5. La qualification ne detecte pas encore DG, DSI, DAF, DRH, RSSI, DPO, QSE, acheteur public, responsable risques.
6. La taxonomie "primary_need" est trop large: CRM, GMAO, SI finance, RFE, SIRH, PCA/PRA, ISO 9001, ISO 27001, MAPSI sont absorbes dans des familles trop generales.
7. Les preuves existent: pages references anonymisees, secteurs, ressources, certification ISO 27001, MAPSI edite par OLING, presence France/DROM. Elles ne sont pas assez exploitees en conversation.
8. MAPSI est bien documente comme SaaS GRC OLING, mais l'agent doit mieux distinguer conseil OLING, demo MAPSI, ou combinaison conseil + outil.
9. Les pages FR sont riches; EN/ES existent et peuvent servir les visiteurs internationaux. L'agent doit conserver la langue du visiteur.
10. Les demandes "prix", "grand cabinet", "freelance", "integrateur deja en place", "urgence", "AO" ne sont pas assez cadrees.
11. Les CTA actuels sont surtout contact generique/tel/email/formulaire; il manque des CTA par maturite projet.
12. Priorite P0: remplacer la doctrine conversationnelle et enrichir le scoring/tests avant production.

## B. Cartographie complete OLING

CONFIRME - Offres principales:
- AMOA SI / transformation SI: cadrage, gouvernance, schemas directeurs, pilotage, direction de projet, PMO.
- AMOA ERP / applications metiers: cadrage, choix ERP/progiciel, cahier des charges, consultation, reprise de donnees, interfaces, recette, conduite du changement.
- CRM, GMAO, SI finance, facturation electronique, infrastructure SI.
- Data, BI, automatisation, IA utile, AMOA IA, gouvernance IA / AI Act.
- RGPD / DPO / gouvernance des donnees: registre, DPIA/AIPD, droits, violations, pilotage DPO.
- Cybersecurite / ISO 27001 / NIS2 / DORA / resilience.
- PCA/PRA / ISO 22301.
- QSE / ISO 9001 / ISO 14001 / Qualiopi.
- Risques, audit, controle interne, conformite reglementaire, direction conformite externalisee.
- DSI externalisee, direction qualite deleguee.
- MAPSI: SaaS GRC edite par OLING.

CONFIRME - Secteurs:
- Industrie, services, secteur public.
- Ressources publiees: portuaire public, eau/assainissement, telecom, territorial/data, CRM sur mesure/cyber, PCA/PRA.
- Couverture France metropolitaine et DROM/COM confirmee par pages OLING/MAPSI.

CONFIRME - Outils / technologies:
- ERP/progiciels: Sage X3, SAP, Divalto, Cegid cites dans synonymes/catalogue; LinkedIn OLING cite ERP, HRIS, payroll, CRM, CMMS, BI/Data, billing/customer systems.
- MAPSI: GRC, risques, controles, audits, RGPD, ISO 9001, ISO 27001, NIS2, DORA, PCA, documents, dashboards, ITSM selon modules.
- Microsoft 365 / MSBI cites en sitemap services.

CONFIRME - Preuves:
- OLING ISO 27001:2022 certifie dans `llms.txt`.
- MAPSI est edite, concu, maintenu par OLING.
- Coordonnees: 01 89 70 15 60, contact@oling.fr.
- LinkedIn: cabinet independant IT consulting, ERP, Finance Systems, HRIS, CRM & Compliance.

PROBABLE:
- SAP/IFS/Microsoft Dynamics/IFS: competences possibles dans univers ERP mais pas toutes confirmees par sources lues. Ne pas affirmer sans page/source.
- DSI de transition: proche DSI externalisee, mais a distinguer si non documente.
- Partenaires editeurs/integrateurs: non prouves.

NON PROUVE:
- References clients nommees.
- Tarifs publics.
- Resultats chiffres garantis.
- Maitrise certifiee de chaque ERP cite.

## C. Audit base de connaissance agent

Il sait:
- offres majeures OLING;
- cadres AMOA ERP, RGPD, cyber, QSE, IA;
- regles de confidentialite clients;
- contact OLING;
- quelques synonymes et secteurs.

Il sait mal:
- personas et role decisionnel;
- granularite offres: GMAO/CRM/SI finance/RFE souvent ramenes a AMOA ERP;
- MAPSI vs conseil;
- objections;
- prix/ordre de grandeur;
- signaux de lead chaud;
- preuves sectorielles.

Il ne sait pas assez:
- parcours AO/marches publics;
- gestion "integrateur en retard";
- DPO partant, controle CNIL, incident securite;
- DSI externalisee/transition;
- posture commerciale differenciee face aux grands cabinets/freelances.

Obsolete/risque:
- Politique RGPD ancienne dans resultats publics mentionne Boulogne; MAPSI/OLING mentionnent aussi Paris/Baie-Mahault. Harmoniser les coordonnees publiques.

## D. Audit commercial conversationnel

Forces:
- ton sobre;
- garde-fous anti-hallucination;
- confidentialite clients;
- contact clair;
- structure courte.

Faiblesses:
- peu de reformulation;
- peu de diagnostic;
- pas assez de preuve proche;
- questions trop generiques;
- CTA trop tardif ou generique;
- qualification insuffisante;
- pas de doctrine prix/objections.

Freins conversion:
- l'utilisateur ne sent pas toujours "ils ont compris mon cas";
- les preuves anonymisees restent sous-utilisees;
- MAPSI peut etre absent quand le besoin logiciel GRC est explicite.

Opportunites:
- transformer chaque reponse projet en mini-consultation: constat, risque, orientation, preuve, 1-2 questions, CTA adapte.

## E. Resultats Mystery Shopper

Grille: comprehension 15, conseil 15, qualification 10, expertise 10, preuves 10, persona 10, differenciation 10, progression vente 10, CTA 5, exactitude 5.

| # | Scenario | Score actuel estime | Defaut principal | Reponse recommandee |
|---|---:|---:|---|---|
| 1 | On veut changer Sage. | 58 | trop generique | cadrer ERP actuel, irritants, modules, donnees, interfaces; proposer echange ERP |
| 2 | Notre ERP devient ingerable. | 60 | pas assez diagnostic | reformuler obsolescence/risque; qualifier perimetre, urgence |
| 3 | Cahier des charges ERP. | 62 | livrables OK mais peu CTA | expliquer ateliers, grille choix, dossier consultation |
| 4 | ERP finance DAF. | 55 | persona DAF absent | parler cloture, reporting, referentiels, controles |
| 5 | Choisir SAP ou Dynamics. | 48 | risque competence non prouvee | expliquer methode de choix sans affirmer expertise produit non sourcee |
| 6 | Vous connaissez Sage X3 ? | 52 | reponse evasive | dire confirme si source; sinon AMOA autour ERP/Sage, questions perimetre |
| 7 | Integrateur ERP en retard. | 57 | pas assez crise | audit flash, gouvernance, reste-a-faire, arbitrage |
| 8 | Reprise donnees/interfaces. | 50 | question trop large | donner plan reprise/interface puis question systemes critiques |
| 9 | GMAO eau assainissement. | 61 | preuve faible en local | citer ressource eau/assainissement GMAO anonymisee |
|10 | CRM sur mesure et cyber. | 60 | preuve peu exploitee | relier CRM/process/data/securite |
|11 | SIRH. | 42 | offre peu documentee | ne pas sur-vendre; orienter AMOA applicative/RH si confirme |
|12 | Facturation electronique. | 51 | detection faible | cadrer e-invoicing, SI finance, PDP/PA, flux |
|13 | SI finance multi-sites. | 54 | pas assez DAF | parler referentiels, consolidation, reporting, interfaces |
|14 | Power BI / reporting. | 55 | data trop large | cadrer indicateurs, sources, gouvernance donnees |
|15 | Application metier specifique. | 57 | manque livrables | cadrage besoin, backlog, architecture, recette |
|16 | Schema directeur SI. | 60 | CTA absent | diagnostic portefeuille, trajectoire, arbitrages |
|17 | DSI externalisee. | 58 | offre peu active | gouvernance DSI, run/projets, comites |
|18 | DSI transition. | 45 | non prouve | parler DSI externalisee/confirmer mission |
|19 | PMO programme. | 56 | trop generique | comitologie, risques, planning, decision |
|20 | Transformation digitale PME. | 60 | banal | prioriser irritants/processus/ROI |
|21 | NIS2 me concerne ? | 62 | conseil OK, conversion faible | criteres d'applicabilite, diagnostic court |
|22 | DORA. | 55 | preuve faible | secteur financier, dependances TIC, plan de conformite |
|23 | ISO 27001 certification. | 66 | preuve ISO peu exploitee | citer certification OLING, SMSI, ecarts, SoA |
|24 | Audit cyber. | 58 | pas assez priorise | posture, risques, feuille route |
|25 | PCA/PRA. | 64 | bon domaine | BIA, RTO/RPO, exercices, dependances |
|26 | ISO 22301. | 55 | peu specifique | lier PCA/PRA et systeme management continuite |
|27 | Controle CNIL. | 55 | urgence faible | plan reponse, preuves, registre, AIPD |
|28 | Mon DPO part. | 52 | lead chaud manque | proposer relais DPO externalise rapide |
|29 | Audit RGPD. | 65 | correct | diagnostic, registre, sous-traitants, DPIA |
|30 | DPIA outil RH. | 57 | persona DRH absent | risques personnes, donnees sensibles, mesures |
|31 | Registre traitements. | 62 | info OK | cadrer processus/responsables/preuves |
|32 | DPO externalise public. | 58 | marche public absent | continuité DPO, gouvernance, calendrier |
|33 | ISO 9001. | 62 | OK | processus, audits, NC, revue direction |
|34 | ISO 14001. | 55 | moins prouve | dire QSE/ISO 14001 si source, sinon prudence |
|35 | Qualiopi. | 57 | MAPSI possible | distinguer conseil Qualiopi et MAPSI |
|36 | QSE plan actions. | 65 | MAPSI pas assez propose | orienter MAPSI si logiciel de pilotage |
|37 | Controle interne. | 60 | OK | risques/controles/preuves/actions |
|38 | GRC outil. | 72 | MAPSI fort | proposer demo MAPSI + cadrage |
|39 | Gestion risques. | 66 | OK | cartographie, cotation, plans |
|40 | Audit interne. | 63 | OK | programme, constats, actions, preuves |
|41 | Logiciel RGPD. | 70 | MAPSI a activer | demo MAPSI + diagnostic dispositif |
|42 | Logiciel ISO 27001. | 68 | MAPSI | actifs, risques SSI, preuves |
|43 | ITSM. | 52 | source MAPSI seulement | orienter MAPSI si module active; prudence |
|44 | IA Act. | 60 | recent | carto usages IA, risques, supervision |
|45 | Agents IA metier. | 58 | preuve faible | AMOA IA: cas d'usage, risques, ROI |
|46 | DG tres amont. | 54 | pas executive | enjeux, options, arbitrages, prochaine etape |
|47 | DSI mature. | 58 | manque technique | architecture, dependances, gouvernance |
|48 | DAF budget. | 50 | prix absent | variables de chiffrage + mini-cadrage |
|49 | DRH SIRH. | 44 | offre insuffisante | prudence, AMOA applicative RH si confirme |
|50 | Acheteur public AO. | 48 | marche public absent | procedure, DCE, criteres, neutralite AMOA |
|51 | Prix AMOA ERP. | 46 | esquive | donner variables: duree, perimetre, ateliers, livrables |
|52 | Petite structure ? | 40 | objection absente | repondre independance, seniorite, proximite, preuves |
|53 | Grand cabinet concurrent. | 42 | differenciation absente | expliquer seniorite, independance, execution |
|54 | Deja integrateur. | 50 | angle AMOA absent | OLING tiers de confiance, recette, arbitrages |
|55 | Freelance suffit ? | 42 | objection absente | comparer capacite, methode, continuite |
|56 | Prix eleve. | 40 | objection absente | parler risque evite, phasage, livrables |
|57 | Pas changer outils. | 55 | bon potentiel | optimisation processus/usage avant remplacement |
|58 | Guadeloupe/DROM. | 66 | OK | confirmer couverture DROM/COM |
|59 | Client nomme Veolia. | 70 | garde-fou OK | ne pas confirmer, parler contexte secteur |
|60 | Urgence incident/projet bloque. | 50 | CTA pas assez rapide | proposer echange rapide et triage prioritaire |

## F. Matrice Persona x Besoin x Offre OLING

| Persona | Signaux | Offre | Questions max | CTA |
|---|---|---|---|---|
| DG | arbitrage, risque, budget | transformation SI, GRC, DSI externalisee | impact, echeance | echange de cadrage direction |
| DSI | SI, architecture, run/projet | AMOA SI, cyber, PCA, data | systemes, dependances | atelier diagnostic |
| DAF | ERP finance, reporting, facture | SI finance, ERP, RFE | cloture/reporting, ERP actuel | cadrage finance/SI |
| DRH | SIRH, donnees RH | AMOA applicative, RGPD | outil, donnees sensibles | cadrage SIRH/RGPD |
| RSSI | ISO27001, NIS2, risques | cyber, ISO27001, MAPSI | perimetre, maturite | diagnostic posture |
| DPO | RGPD, DPIA, registre | RGPD/DPO/MAPSI | urgence, preuves | relais DPO/cadrage |
| QSE | ISO/QSE/actions | QSE, MAPSI | referentiels, audit | demo MAPSI ou audit QSE |
| Risques/CI | GRC, controles | risques, controle interne, MAPSI | referentiels, comites | cadrage GRC |
| Acheteur public | AO, DCE | AMOA, achats/marches | procedure, calendrier | relecture DCE/cadrage |

## G. Matrice Besoin x Preuve

| Besoin | Preuve utilisable | Statut |
|---|---|---|
| AMOA ERP | pages ERP/progiciel, business-apps/erp, questionnaire ERP | CONFIRME |
| GMAO eau/assainissement | ressource AMOA GMAO eau assainissement | CONFIRME |
| CRM/cyber | ressource CRM sur mesure/cyber | CONFIRME |
| SI finance | page SI finance | CONFIRME |
| RGPD/DPO | pages RGPD, expertise RGPD-DPO, ressources DPIA/registre | CONFIRME |
| ISO 27001 | page cyber, llms mention ISO 27001:2022 | CONFIRME |
| NIS2/DORA | ressources NIS2/DORA, MAPSI | CONFIRME |
| QSE/ISO9001 | pages conseil qualite/QSE, MAPSI ISO9001 | CONFIRME |
| PCA/PRA | page PCA/PRA, MAPSI PCA | CONFIRME |
| GRC/MAPSI | MAPSI.fr fonctionnalites/a propos | CONFIRME |
| SIRH | LinkedIn cite HRIS/payroll | CONFIRME externe, profondeur OLING.fr faible |
| IFS/Dynamics | non observe dans sources lues | NON PROUVE |

## H. Analyse concurrentielle

Univers AMOA/ERP: grands cabinets, ESN/integrateurs ERP, freelances AMOA. Promesse concurrente: capacite, methodes, reseau editeurs. Reponse OLING: independance AMOA, seniorite, proximite direction/metiers, execution de bout en bout, anonymisation des references.

Univers RGPD/DPO: cabinets juridiques, DPO freelances, logiciels privacy. Reponse OLING: articulation gouvernance + SI + preuves + MAPSI si besoin outil.

Univers cyber/ISO: cabinets cyber techniques, MSSP, auditeurs. Reponse OLING: gouvernance, risques, ISO/NIS2/DORA, continuité, lien direction.

Univers QSE/GRC: cabinets qualite, editeurs GRC. Reponse OLING: conseil + logiciel MAPSI edite par OLING, plans d'actions et preuves.

Univers DSI/transformation: DSI de transition, cabinets strategie, ESN. Reponse OLING: cadrage operationnel, gouvernance projet, AMOA et conformite.

## I. Parcours de conversion cible

1. Identifier intention: information, exploration, projet, lead chaud, urgence, prix.
2. Reformuler en 1 phrase.
3. Donner une orientation utile immediatement.
4. Relier a 1 ou 2 offres OLING, pas plus.
5. Ajouter une preuve proche si disponible.
6. Poser 1 ou 2 questions maximum.
7. Si signal fort: proposer l'echange humain adapte.

## J. Bibliotheque de CTA

1. ERP amont: "Un echange de 30 minutes permettrait de valider perimetre, irritants et strategie de consultation."
2. ERP choisi: "L'echange utile porte surtout sur reprise, interfaces, recette et gouvernance integrateur."
3. ERP bloque: "Vu le risque projet, un diagnostic flash du reste-a-faire serait plus utile qu'une reponse generale."
4. Cahier charges: "Vous pouvez transmettre votre expression de besoin; OLING peut pointer les angles morts."
5. Sage X3: "On peut cadrer les modules, donnees et interfaces avant d'engager la suite."
6. SI finance: "Un cadrage DAF/DSI clarifierait cloture, reporting, referentiels et controles."
7. RFE: "Un echange court permet de situer vos flux, vos outils et vos jalons."
8. GMAO: "Un atelier maintenance/donnees equipements securiserait le besoin."
9. CRM: "Un cadrage parcours, donnees et securite evite de choisir trop vite l'outil."
10. Data/BI: "On peut commencer par cartographier indicateurs, sources et proprietaires."
11. DSI: "Un diagnostic de gouvernance DSI permet de prioriser run, projets et risques."
12. PMO: "Un point programme peut objectiver planning, risques et arbitrages."
13. RGPD: "Un audit court peut dire si le dispositif est documente ou seulement declare."
14. DPO depart: "Un relais DPO peut etre qualifie rapidement avant rupture de continuite."
15. CNIL: "Il faut prioriser preuves, registre, AIPD et historique des demandes."
16. DPIA: "Un echange permet d'arbitrer si l'AIPD est obligatoire et comment la mener."
17. ISO27001: "Un diagnostic SMSI/ecarts permet de mesurer le chemin vers certification."
18. NIS2: "Un cadrage d'applicabilite et de priorites evite un plan trop large."
19. DORA: "Un atelier dependances TIC et preuves de controle est la bonne premiere etape."
20. PCA: "On peut qualifier BIA, RTO/RPO, applications critiques et exercices."
21. QSE: "Un diagnostic QSE peut prioriser processus, audits et plans d'actions."
22. Qualiopi: "On peut distinguer preparation audit et outillage de suivi."
23. GRC logiciel: "Une demo MAPSI est pertinente si vous cherchez un outil de pilotage."
24. MAPSI: "Un cadrage de 30 minutes suffit a voir quels modules activer."
25. Controle interne: "Un atelier risques/controles/preuves donnera une feuille de route."
26. Acheteur public: "Une relecture DCE peut securiser criteres, perimetre et livrables."
27. Prix: "On peut donner une fourchette apres perimetre, duree, livrables et charge ateliers."
28. Petite structure: "Un echange direct avec un consultant senior permettra de juger l'adequation."
29. Urgence: "Le plus efficace est de basculer vers un consultant pour trier les priorites."
30. International: "Nous pouvons poursuivre en francais ou anglais selon votre equipe."

## K. Objections commerciales

1. Petite structure: independance, seniorite, proximite, moins d'intermediation.
2. Grand cabinet: OLING est pertinent si besoin d'execution senior et cadrage pragmatique.
3. Freelance: utile ponctuellement; OLING apporte methode, continuite, capitalisation.
4. Integrateur deja choisi: OLING securise la position metier et les arbitrages.
5. Prix eleve: comparer au cout d'un mauvais cadrage/reprise/recette.
6. Pas changer outils: commencer par processus, usage, donnees, gouvernance.
7. Pas de budget: cadrage leger pour objectiver budget.
8. Trop tot: precisement le bon moment pour eviter un mauvais choix.
9. Trop tard: audit flash pour reprendre controle.
10. Confidentialite: references anonymisees par secteur et mission.
11. Besoin local DROM: couverture France/DROM confirmee.
12. Besoin juridique: OLING cadre operationnel, peut articuler avec juridique.
13. Besoin technique pur: cadrer gouvernance/risques, integrer experts techniques si besoin.
14. Editeur propose deja conseil: AMOA independante evite biais solution.
15. Pas envie questionnaire: 2 questions max, valeur immediate.
16. On sait deja quoi faire: securiser risques oublies.
17. MAPSI seulement? demo si besoin outil; conseil si besoin methode.
18. Urgence reglementaire: prioriser preuves et actions critiques.
19. Donnees sensibles: cadrer securite/RGPD avant partage.
20. Peur lourdeur: intervention phasable.

## L. Recommandations contenus OLING.fr

P0: page "agent IA / prise de contact" avec doctrine et preuves par besoin. Impact: l'agent manque de sources CTA contextualisees.
P0: renforcer pages prix/modes d'intervention avec variables de chiffrage. Impact: demandes prix mal traitees.
P0: creer pages objections/differenciation OLING vs integrateur/grand cabinet/freelance. Impact: conversion.
P1: enrichir SIRH, DSI transition, IFS/Dynamics si competences confirmees. Impact: eviter hallucination.
P1: harmoniser coordonnees publiques historiques. Impact: confiance.
P1: matrice references anonymisees besoin/secteur/preuve. Impact: preuves en conversation.
P2: fiches persona DAF/DSI/DPO/RSSI/QSE/acheteur public.
P2: bibliotheque cas d'usage MAPSI vs conseil.

## M. Backlog d'amelioration

| Prio | Probleme | Impact | Solution | Effort |
|---|---|---|---|---|
| P0 | Doctrine prompt trop FAQ | conversion faible | system prompt V2 ci-dessous | M |
| P0 | Tests 20 non scores | risque regression | 60 scenarios + score /100 | M |
| P0 | Personas absents | reponses peu ciblees | ajouter detection persona | M |
| P0 | CTA generiques | leads perdus | bibliotheque CTA contextualises | S |
| P1 | Taxonomie trop large | mauvaise orientation | besoins granularises | M |
| P1 | MAPSI mal arbitre | demo ratee ou abusive | regles OLING/MAPSI | S |
| P1 | Preuves peu reliees | manque confiance | matrice preuve/retrieval | M |
| P1 | Prix non traite | frustration | doctrine variables/fourchettes | S |
| P2 | Objections absentes | perte leads | scripts objections | S |
| P2 | Sources externes faibles | preuve limitee | enrichir corpus LinkedIn/MAPSI/public | M |
| P3 | EN/ES agent | experience globale | prompt localise | M |

## N. SYSTEM PROMPT V2

Retourne strictement un JSON conforme au schema demande.

Tu es le consultant avant-vente digital d'OLING Management & Technologie.

Objectif: transformer une conversation pertinente en comprehension, confiance, preuve, orientation et prise de contact qualifiee. Tu n'es pas un moteur FAQ. Tu conseilles comme un consultant senior: direct, concret, factuel, sobre.

Regles absolues:
- utilise uniquement les informations OLING/MAPSI fournies dans le contexte;
- n'invente jamais client nomme, certification, partenaire, prix, technologie maitrisee, resultat ou delai;
- ne confirme ni n'infirme jamais une relation avec une organisation nommee;
- si une preuve manque: dis-le simplement et propose le cadrage utile;
- 2 questions maximum par reponse;
- reponses courtes: 2 a 5 blocs, puces si utile;
- pas de discours publicitaire generique.

Logique de chaque reponse:
1. Reponds a la demande.
2. Reformule le besoin en 1 phrase si le contexte projet existe.
3. Donne une orientation concrete.
4. Rattache seulement les offres OLING pertinentes.
5. Ajoute 1 preuve proche si les snippets le permettent.
6. Pose 1 ou 2 questions qualifiantes a forte valeur.
7. Si signal commercial fort, propose un contact contextualise.

Detection persona:
- DG: arbitrage, risque, budget, priorites.
- DSI/RSSI: SI, cyber, architecture, continuité, dependances.
- DAF: ERP finance, cloture, reporting, facturation, controles.
- DRH: SIRH, paie, donnees RH.
- DPO/juridique: RGPD, AIPD, CNIL, contrats, droits.
- QSE/qualite: ISO, audit, non-conformites, plans d'actions.
- Risques/controle interne: GRC, cartographies, controles, preuves.
- Acheteur public: marche, AO, DCE, consultation.

Signaux lead chaud:
projet en cours, AO, choix solution, ERP a remplacer, integrateur en retard, audit/mise en conformite, certification, NIS2/DORA, DPO partant, controle CNIL, incident, budget, planning, urgence, demande devis, demande RDV.

CTA:
- ne force pas le contact pour une question informationnelle simple;
- propose un echange quand le besoin est concret;
- contextualise toujours: ERP, RGPD, cyber, MAPSI, QSE, DSI, AO, urgence;
- si contact direct demande: telephone 01 89 70 15 60, email contact@oling.fr, formulaire /contact?chat_fallback=1.

MAPSI:
- MAPSI est pertinent si le besoin porte sur un logiciel de pilotage GRC/conformite/risques/RGPD/ISO/NIS2/DORA/PCA/audit/controle interne/QSE/actions/preuves.
- Si le besoin est methode, audit, organisation, reglementation ou gouvernance, garde OLING comme interlocuteur principal.
- Si les deux sont presents, propose: cadrage OLING puis demo MAPSI ciblee.

Prix:
- n'elude pas;
- si aucun tarif public n'est fourni, explique les variables: perimetre, maturite, nombre d'ateliers, livrables, sites, donnees, interfaces, urgence, niveau d'accompagnement;
- propose une estimation apres qualification.

Objections:
- petite structure: seniorite, independance, proximite, execution;
- grand cabinet: OLING est adapte si besoin de cadrage senior pragmatique;
- integrateur deja present: OLING joue AMOA independante cote metier;
- freelance: utile ponctuellement, mais OLING apporte methode, continuite, capitalisation;
- prix: ramener au risque evite et proposer phasage.

Qualification a retourner:
primary_need: transformation_si, amoa_erp, crm, gmao, si_finance, rfe, rgpd, cybersecurite, pca_pra, qse, grc_mapsi, ia_data_automatisation, organisation_gouvernance, autre.
urgency_level: immediate, short_term, planned, exploratory.
maturity_level: flou, cadre, consultation, en_cours, bloque.
organization_type: pme, pmi, eti, public, association, autre.
organization_size: 1_49, 50_249, 250_999, 1000_plus, unknown.
commercial_intent: information, orientation, diagnostic, cadrage, assistance_projet, audit, mise_en_conformite, demo_logiciel, devis, contact.
potential_value: low, medium, high.

Exemple de posture:
"Si votre ERP est deja choisi, le sujet n'est probablement plus le choix de solution. Il faut surtout securiser le cadrage fonctionnel, les interfaces, la reprise de donnees, la recette et la conduite du changement. C'est typiquement sur cette phase qu'une AMOA independante reduit le risque projet. Quel ERP est concerne et ou en etes-vous: cadrage, consultation, deploiement ou projet bloque ?"

## Verification finale

- Sitemap verifie: `sitemap.xml`, practice, services, default, EN, ES.
- Pages non decouvertes par navigation: services legacy `/consulting/*`, `/business-apps/*`, `/mapsi/*`, ressources.
- Sources externes verifiees: MAPSI.fr, LinkedIn OLING, Pappers, Kompass, CINOV.
- References: anonymisees uniquement, par secteur/mission.
- Expertises: cartographie ci-dessus.
- Concurrents: analyses par univers.
- Parcours conversion: defini.

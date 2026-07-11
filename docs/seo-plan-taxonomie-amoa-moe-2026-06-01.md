# Plan SEO contenu + taxonomie - oling.fr

Date: 1 juin 2026

## 1) Objectif SEO business

Ameliorer le positionnement organique et la generation de leads sur 2 grappes d'intention:
- AMOA ERP CRM GMAO
- AMOA MOE conformite RGPD ISO

Objectif 90 jours:
- Augmenter la visibilite sur les requetes non-marquees transactionnelles.
- Transformer les pages expertise en pages de conversion (contact qualifie).

## 2) Constat rapide (base existante)

Pages deja en place et utiles:
- `/amoa-si`
- `/erp-progiciel`
- `/crm`
- `/gmao`
- `/rgpd`
- `/conseil-qualite`
- `/cyber-securite`
- `/ressources` + articles

## 2.1) Audit BDD production (snapshot du 1 juin 2026)

Tables et volumes constates:
- `practice`: 4 lignes (`consulting`, `expertises-audit`, `business-apps`, `mapsi`).
- `services`: 31 lignes.
- `site_page`: 4 lignes (`apropos`, `metiers`, `client`, `team`).
- `legal_page`: 3 lignes (`polrgpd`, `polsecurite`, `mentions-legales`).

Constats SEO/taxonomie issus de la BDD:
- Le noeud AMOA principal cote BDD reste `practice.slug = consulting` (historique), pas `amoa-si`.
- L'intention ERP/CRM/GMAO est principalement concentree dans le service `business-apps/erp` (designation: "ERP, CRM, GMAO...").
- Il n'existe pas de service dedie `crm` ni `gmao` en BDD (seulement des landings SEO statiques cote templates/routes).
- Il n'existe pas de noeud explicite "MOE" (ni `practice`, ni `service` dedie).
- Les contenus RGPD/ISO existent bien via `expertises-audit/rgpd`, `expertises-audit/qse`, `expertises-audit/si` et les pages legales.

Impacts:
- Risque de cannibalisation entre URLs dynamiques historiques (`/consulting/...`, `/business-apps/erp`) et landings SEO (`/amoa-si`, `/crm`, `/gmao`).
- Besoin d'une strategie canonique claire avant production de nouveaux contenus.
- Priorite a la liaison entre taxonomie BDD (practice/services) et taxonomie SEO (landings/ressources).

Gaps prioritaires:
- Pas de page dediee "AMOA MOE".
- Pas de page pilier dediee "conformite ISO" (ISO 27001 / 9001 selon cible).
- ERP/CRM/GMAO encore fusionnes dans la taxonomie BDD.

## 3) Taxonomie cible (pilier -> cluster -> preuve)

## Piliers (niveau 1)
- `/amoa-si` (hub principal)
- `/amoa-moe` (nouveau)
- `/erp-progiciel` (a orienter "AMOA ERP")
- `/crm` (a orienter "AMOA CRM")
- `/gmao` (a orienter "AMOA GMAO")
- `/rgpd`
- `/conformite-iso` (nouveau, avec sections ISO 27001 et ISO 9001)

## Clusters (niveau 2)
- AMOA ERP: cadrage, choix solution, conduite du changement, recette.
- AMOA CRM: processus commerciaux, reporting, adoption.
- AMOA GMAO: maintenance, indicateurs, gouvernance actifs.
- AMOA MOE: interface AMOA/MOE, gouvernance, livrables, RACI.
- RGPD: registre, AIPD, sous-traitants, droits des personnes.
- ISO: SMSI, analyse de risque, plan d'audit, non-conformites.

## Entites de preuve (niveau 3)
- Cas clients par secteur (public, PME, ETI) avec resultats chiffres.
- Methodes/livrables telechargeables (checklist, matrice RACI, scorecard).
- Certifications et references (ISO, DPO, etc.) visibles sur chaque pilier.

## 4) Mapping mots-cles -> URL canonique

- `amoa erp` -> `/erp-progiciel`
- `amoa crm` -> `/crm`
- `amoa gmao` -> `/gmao`
- `amoa moe` -> `/amoa-moe`
- `amoa moe rgpd` -> `/amoa-moe` + lien fort vers `/rgpd`
- `conformite rgpd` -> `/rgpd`
- `conformite iso 27001` -> `/conformite-iso`
- `amoa conformite` -> `/amoa-si`

Regle anti-cannibalisation:
- 1 mot-cle principal par page.
- 1 URL canonique unique par intention.

## 5) Plan contenu 90 jours (publishing)

## Vague 1 (1-15 juin 2026): architecture + quick wins
- Creer la page pilier `/amoa-moe`.
- Creer la page pilier `/conformite-iso`.
- Repositionner H1/title/meta de `/erp-progiciel`, `/crm`, `/gmao` avec angle "AMOA".
- Ajouter FAQ (3-5 questions) sur les 7 pages piliers.
- Renforcer CTA unique vers `/contact` sur chaque page pilier.

## Vague 2 (16 juin-31 juillet 2026): clusters transactionnels
- Publier 8 contenus:
  - `amoa-erp-cadrage-livrables`
  - `amoa-crm-erreurs-a-eviter`
  - `amoa-gmao-indicateurs-kpi`
  - `amoa-moe-gouvernance-raci`
  - `rgpd-plan-90-jours`
  - `aipd-mode-operatoire`
  - `iso-27001-feuille-route`
  - `audit-interne-iso-non-conformites`
- Chaque article: 1200-1800 mots, 4+ liens internes, 1 CTA principal.

## Vague 3 (aout 2026): autorite + conversion
- Ajouter 4 cas clients (ERP, CRM, GMAO, conformite RGPD/ISO).
- Creer 2 pages sectorielles longue traine (secteur public, PME/ETI).
- Tester variantes de titles/metas pour ameliorer CTR sur pages a fort volume d'impression.

## 6) Maillage interne cible

Menu/footer:
- Lier explicitement: AMOA SI, AMOA MOE, ERP, CRM, GMAO, RGPD, Conformite ISO.

Liens contextuels obligatoires:
- `/amoa-si` -> `/amoa-moe`, `/erp-progiciel`, `/crm`, `/gmao`, `/rgpd`, `/conformite-iso`.
- `/amoa-moe` -> `/amoa-si`, `/rgpd`, `/conformite-iso`, `/contact`.
- `/rgpd` <-> `/conformite-iso` <-> `/cyber-securite` (triangle conformite/securite).
- Articles clusters -> page pilier correspondante + `/contact`.

Ponts obligatoires entre BDD et SEO:
- Depuis `/consulting` et `/consulting/assistance-a-maitrise-douvrage` -> lien fort vers `/amoa-si` et futur `/amoa-moe`.
- Depuis `/business-apps/erp` -> liens forts vers `/erp-progiciel`, `/crm`, `/gmao`.
- Depuis `/expertises-audit/rgpd` et `/expertises-audit/qse` -> liens vers `/rgpd` et futur `/conformite-iso`.
- Depuis les pages legales (`polrgpd`, `polsecurite`) -> CTA discret vers pages conseil correspondantes.

## 7) Technique SEO a executer sur le VPS

- Regenerer et soumettre sitemap apres ajout des nouvelles pages.
- Verifier 301/canonical pour eviter doublons (`/practice/...`, aliases historiques).
- Verifier robots/noindex sur admin, login, pages de test.
- Controler les logs 404 sur 30 jours pour corriger liens morts.
- Verifier Core Web Vitals sur pages piliers (LCP/CLS/INP) et optimiser media si besoin.

## 8) KPI de pilotage (hebdo + mensuel)

Hebdomadaire:
- Impressions/clics/CTR par URL pilier.
- Position moyenne sur mots-cles cibles.
- Nombre de leads SEO (formulaire contact).

Mensuel:
- Nombre de mots-cles Top 3 / Top 10.
- Part de trafic non-marque.
- Taux de conversion SEO vers contact.

Cibles 90 jours:
- +30% impressions SEO sur les pages cibles.
- +20% clics SEO non-marques.
- 6 a 10 mots-cles prioritaires en Top 10.

## 9) Backlog priorise (ordre d'execution)

1. Verifier et figer la strategie canonique entre URLs historiques BDD (`/consulting`, `/business-apps/erp`) et landings SEO (`/amoa-si`, `/erp-progiciel`, `/crm`, `/gmao`).
2. Creer `/amoa-moe` (pilier) + maillage depuis `consulting`.
3. Creer `/conformite-iso` (pilier) + maillage depuis `expertises-audit` et `polsecurite`.
4. Desambiguiser ERP/CRM/GMAO: garder `business-apps/erp` comme preuve/offre globale et pousser les intentions transactionnelles vers les landings dediees.
5. Rewriter titles/H1/metas de `/erp-progiciel`, `/crm`, `/gmao` avec prefixe AMOA.
6. Ajouter FAQ schema (FAQPage) sur tous les piliers.
7. Publier 8 articles clusters.
8. Ajouter 4 cas clients chiffres.
9. Ajuster titles/metas selon data Search Console.

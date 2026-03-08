## Pourquoi trop de KPI tue la qualite

Dans beaucoup d'organisations, les tableaux de bord SI accumulent des dizaines d'indicateurs. L'intention est bonne, mais le resultat est contre-productif: personne ne sait vraiment quels KPI orientent les decisions. La qualite SI ne progresse pas avec plus de chiffres; elle progresse avec des indicateurs pertinents, compris et utilises dans les instances de pilotage.

Un bon indicateur doit relier trois dimensions: performance operationnelle, risque et valeur metier. Si un KPI est techniquement precise mais sans impact decisionnel, il surcharge les equipes. Inversement, un KPI tres "direction" mais non mesurable cree des discussions abstraites sans plan d'action.

Le premier travail consiste donc a nettoyer le portefeuille de KPI. Gardez ceux qui permettent d'arbitrer: prioriser un chantier, corriger un processus, renforcer un controle, ajuster une ressource. Ce tri est un levier puissant de maturite managériale.

Pour les acteurs publics, ce recentrage favorise la transparence vis-a-vis des usagers et des tutelles. Pour les PME, il facilite des decisions rapides sans structure lourde. Pour les ETI, il harmonise les pratiques entre entites et limite les reportings contradictoires.

## 12 indicateurs qualite SI a suivre

Voici un socle de 12 KPI utiles dans la majorite des contextes:
- Disponibilite des services critiques.
- Delai moyen de resolution des incidents majeurs.
- Taux de respect des SLA contractuels.
- Taux de changement reussi sans incident post-prod.
- Delai de correction des vulnerabilites critiques.
- Taux de restoration reussie des sauvegardes testees.
- Taux de conformite des acces (revues droits).
- Taux de demandes utilisateurs resolues au premier contact.
- Taux de satisfaction utilisateurs (CSAT/NPS interne).
- Taux d'actions d'audit/cloture dans les delais.
- Taux de completion des actions d'amelioration continue.
- Cout de non-qualite SI (incidents, rework, interruptions).

Ce socle couvre l'essentiel: service, securite, conformite, experience utilisateur et performance economique. Vous pouvez l'ajuster selon votre contexte, mais evitez de depasser 15 indicateurs en pilotage direction.

## Construire un tableau de bord utile a la direction

Un tableau de bord direction doit etre lisible en moins de cinq minutes. Cela implique:
- Une vue tendance (mois N, N-1, cible).
- Un code couleur sobre (attention a la sur-signaletique).
- Un commentaire court par KPI critique.
- Une section "decisions attendues".

Le commentaire est essentiel. Un KPI sans interpretation peut induire des mauvaises decisions. Exemple: un delai incident qui baisse peut masquer une hausse d'incidents severes. Il faut donc donner le contexte et les causes.

Ajoutez une section "risques residuels" qui relie KPI et exposition metier. Cette articulation aide la direction a arbitrer budget et priorites en connaissance de cause.

## Relier KPI qualite, risques et conformite

La qualite SI est souvent geree en silo, separee de la conformite et de la securite. C'est une erreur. Un incident de disponibilite peut devenir un incident RGPD. Une mauvaise gouvernance de changement peut ouvrir une faille cyber. Un retard d'action corrective peut fragiliser un audit.

Relier les KPI entre eux permet d'anticiper ces effets domino. Exemple concret:
- Baisse du taux de changement reussi.
- Hausse des incidents post-production.
- Baisse de satisfaction utilisateurs.
- Hausse du cout de non-qualite.

Ce chainage rend visible la causalite et facilite les actions racines. C'est la base d'une demarche d'amelioration continue utile, pas cosmetique.

## Rythmes de revue et amelioration continue

Un dispositif de KPI n'a de valeur que si les revues sont regulieres et orientees action. Rythme recommande:
- Hebdomadaire operationnel (30 min): incidents, actions critiques, blocages.
- Mensuel management (60 min): tendances, causes racines, priorites.
- Trimestriel direction (45 min): trajectoire, investissements, risques acceptes.

Chaque revue doit produire des decisions avec responsables et echeances. Sans cela, le tableau de bord devient un rituel sterile.

L'amelioration continue fonctionne bien avec un cycle court: detecter, analyser, corriger, verifier, standardiser. Ce cycle est compatible avec les pratiques AMOA SI et facilite l'integration dans les projets de transformation.

## Exemple de scorecard prete a l'emploi

Vous pouvez structurer votre scorecard sur quatre quadrants:
- Service: disponibilite, SLA, incidents.
- Securite/conformite: acces, vulnerabilites, actions audit.
- Experience utilisateur: satisfaction, delais de prise en charge.
- Efficience: cout de non-qualite, taux de rework, productivite support.

Pour chaque KPI, fixez une cible annuelle et des seuils d'alerte trimestriels. Ajoutez un proprietaire unique par indicateur, responsable de l'analyse et du plan d'action.

Cette approche cree une responsabilite claire et evite les discussions diluees entre equipes.

## Cas pratique: PME de services en reprise de controle

Une PME de services numeriques suivait plus de 40 KPI sans reelle utilite. Les comites mensuels etaient longs, peu conclusifs, et les incidents recurrentes continuaient. L'organisation a ramene son tableau de bord a 12 KPI, avec commentaires standardises et decisions explicites.

En trois mois, elle a reduit les incidents majeurs, ameliore le taux de resolution au premier contact et baisse le cout de non-qualite. Le gain principal a ete managerial: les responsables savaient enfin sur quoi agir.

Ce cas montre qu'un dispositif simple, bien tenu, peut produire des effets rapides, meme avec des ressources limitees.

## FAQ rapide

### Faut-il mesurer la qualite SI tous les jours ?

Pas tous les KPI. Certains se suivent en temps reel (incidents), d'autres en hebdo ou mensuel.

### Quel KPI est le plus important ?

Celui qui connecte directement un risque metier prioritaire a une action pilotable.

### Peut-on comparer des KPI entre filiales ?

Oui, si les definitions sont harmonisees et les methodes de collecte identiques.

## Pour aller plus loin

Connectez ces KPI a votre trajectoire [Conseil qualite](/conseil-qualite), puis articulez les decisions de transformation avec [AMOA SI](/amoa-si). Pour les enjeux de securite associes, voir [Cyber securite](/cyber-securite). Pour cadrer votre dispositif de pilotage, contactez-nous via [Contact OLING](/contact).

## Gouvernance des donnees KPI: fiabilite avant affichage

Un tableau de bord qualite SI perd toute credibilite quand les donnees source sont instables. Avant de discuter interpretation, il faut donc securiser la chaine de production des KPI. Cela commence par une definition unique de chaque indicateur: formule, source, frequence, responsable, regles d'exclusion. Sans ce dictionnaire, deux equipes peuvent afficher le meme KPI avec deux calculs differents.

Ensuite, mettez en place un controle qualite des donnees de reporting. Quelques controles simples suffisent: valeurs manquantes, variations anormales, coherence temporelle, duplication, changements de perimetre non documentes. Quand une anomalie apparait, le commentaire du KPI doit l'indiquer clairement. Il vaut mieux reconnaitre un doute de mesure que presenter une precision illusoire.

Pour les ETI, la gouvernance des donnees KPI doit etre federative: standards centraux, collecte locale, validation periodique. Pour les PME, une gouvernance legere avec un proprietaire par KPI et une revue mensuelle est souvent suffisante. Dans le secteur public, cette discipline renforce la transparence des revues de performance et la qualite des arbitrages institutionnels.

## Plan 90 jours pour mettre en place un pilotage KPI utile

### Semaine 1 a 4

Selectionnez 12 KPI maximum, validez les definitions et formalisez les proprietaires. Construisez une baseline sur trois mois si possible. Choisissez un format de tableau unique pour tous les comites.

### Semaine 5 a 8

Lancez les revues mensuelles avec commentaires obligatoires par KPI critique. Associez chaque signal d'alerte a une action, un responsable et une echeance. Commencez a suivre les causes racines des ecarts recurrents.

### Semaine 9 a 12

Stabilisez le rythme: revue operationnelle, revue management, revue direction. Supprimez les indicateurs inutiles. Renforcez ceux qui produisent des decisions utiles. En fin de cycle, partagez une synthese "gains obtenus / risques residuels / priorites suivant trimestre".

Ce plan court permet d'installer une culture de pilotage par la valeur sans alourdir les equipes. Il transforme progressivement le reporting en mecanisme de progression.

## Evaluer rapidement la maturite cyber

Une feuille de route cyber securite utile commence par une evaluation lucide de la maturite existante. Beaucoup de PME et d'ETI sautent cette etape et lancent des actions dispersees: un outil EDR ici, une politique de mot de passe la, une sensibilisation ponctuelle ailleurs. Le resultat est souvent decevant, car les investissements ne traitent pas les causes racines ni les risques les plus probables.

L'evaluation doit rester pragmatique. Inutile de deployer un audit de six mois. Un diagnostic de deux a quatre semaines permet deja de couvrir les dimensions critiques: gouvernance, identites et acces, postes de travail, serveurs et cloud, sauvegardes, supervision, gestion des fournisseurs, gestion de crise, culture interne. Le but est de produire une photographie exploitable pour decider.

Concretement, vous pouvez noter chaque domaine sur une echelle simple de 1 a 5 et associer des preuves minimales. Exemple: "sauvegardes testeess mensuellement" vaut plus qu'une declaration d'intention. Cette approche evidence-based evite les debats subjectifs et donne une base commune entre DSI, direction et metiers.

Pour les ETI, ajoutez une lecture par entite ou par filiale. Les ecarts de maturite internes sont souvent plus importants que prevu. Pour les PME, concentrez-vous sur les actifs qui portent le chiffre d'affaires, la relation client et la continuite de service. Cette priorisation est la cle d'une feuille de route realiste.

## Definir les priorites (risques, impacts, obligations)

Une fois la maturite evaluee, il faut prioriser. Le meilleur schema combine trois angles: risque probable, impact metier, et obligations reglementaires/contractuelles. Une action peut etre techniquement pertinente mais peu prioritaire si son impact business est limite. A l'inverse, une faiblesse simple peut devenir prioritaire si elle expose une activite critique ou un engagement client majeur.

La priorisation gagne en clarte avec une matrice 3x3: probabilite d'incident, severite d'impact, exposition reglementaire. Chaque chantier est place dans cette matrice puis classe en trois niveaux: immediate, planifiee, opportuniste. Cela permet d'eviter le piege du "tout urgent" qui paralyse les equipes.

N'oubliez pas les obligations externes. Entre exigences clients, attentes assureurs, normes internes, RGPD et cadres comme NIS2/DORA selon les cas, la pression de conformite peut etre forte. La bonne strategie consiste a transformer ces obligations en controles operationnels, pas en documentation decorative. Une preuve de controle actif vaut toujours mieux qu'un dossier volumineux mais inerte.

## Plan d'actions 0-3 mois, 3-6 mois, 6-12 mois

### 0-3 mois: stopper les risques evitables

La premiere phase cible les quick wins a fort impact. Typiquement: MFA sur comptes privilegies, revue des comptes inactifs, durcissement des acces distants, segmentation minimale des droits admin, sauvegardes hors ligne, test de restauration, inventaire des actifs critiques, patchs des vulnerabilites exposees internet. Ces actions reduisent rapidement la surface d'attaque.

Ajoutez une campagne de sensibilisation orientee scenarios reels: phishing, fraude au president, fuite de donnees, erreur de partage cloud. L'objectif n'est pas de "former pour former", mais de changer quelques comportements a risque qui provoquent la majorite des incidents.

### 3-6 mois: structurer les fondations

La deuxieme phase consolide la gouvernance et les processus. Mettez en place un comite cyber court mensuel, formalisez les roles, documentez la politique SSI, standardisez les exigences fournisseurs critiques, installez un processus de gestion des vulnerabilites et un cycle de patch management mesure.

Cote supervision, commencez par un socle simple: centralisation des journaux essentiels, alertes sur evenements critiques, scenarios de detection prioritaires, et procedure de traitement incident. Inutile de viser la perfection SOC des le depart. Visez une detection plus rapide et une reaction coordonnee.

### 6-12 mois: industrialiser et piloter

La troisieme phase installe la durabilite. Automatisez les controles repetitifs, integrez la cyber aux projets AMOA SI, ajoutez des tests de crise, professionnalisez le plan de reprise et mesurez la performance via KPI. C'est aussi la phase ou vous alignez plus finement la cyber avec qualite, continute d'activite et performance operationnelle.

A ce stade, la feuille de route ne doit plus etre un document statique. Elle devient un portefeuille de chantiers pilote, avec arbitrages trimestriels et revues de priorite en fonction des menaces et du contexte business.

## Budget, ressources et arbitrages

La question budget revient toujours. Le point cle est de raisonner en cout de risque evite et en cout de non-qualite. Une interruption de service, une fuite de donnees ou une perte de confiance client coute souvent bien plus cher que les mesures de prevention.

Pour une PME, la bonne approche est souvent de combiner internalisation des decisions et externalisation selective de l'execution. Vous gardez la maitrise des priorites et vous confiez certains chantiers techniques a des partenaires selon les besoins. Pour une ETI, la logique est plus mixte: equipe interne structuree, expertises externes ponctuelles, et gouvernance transverse.

Les arbitrages doivent etre explicites. Quand une action est differee, documentez le risque accepte, la raison, et la date de re-evaluation. Cette discipline evite les zones grises et professionnalise la relation avec la direction.

## KPI cyber pour piloter en comite

Un comite cyber efficace s'appuie sur peu d'indicateurs, mais des indicateurs utiles. Exemples:
- Taux de MFA sur comptes sensibles.
- Delai moyen de correction des vulnerabilites critiques.
- Taux de restauration reussie des sauvegardes testees.
- Nombre d'incidents detectes en moins de 24h.
- Pourcentage de fournisseurs critiques evalues.
- Taux de completion des actions de feuille de route.

Ajoutez une mesure d'exposition residuelle par domaine. L'objectif n'est pas d'atteindre zero risque, impossible en pratique, mais de montrer une reduction tangible et continue.

L'important est la regularite de lecture. Un KPI consulte une fois par trimestre n'aide pas a piloter. Un tableau de bord mensuel court, commente et relie aux decisions est beaucoup plus performant.

## Exemple de gouvernance SSI pour PME/ETI

Modele simple et efficace:
- Sponsor direction: arbitre, porte les priorites, valide les risques acceptes.
- Responsable SI/SSI: pilote la feuille de route et coordonne l'execution.
- Referents metier: remontent les contraintes et valident les impacts operationnels.
- DPO/qualite/juridique selon contexte: assurent la coherence conformite.
- Partenaires externes: execution technique et expertise specialisee.

Rythme recommande:
- Hebdomadaire operationnel (30 min): incidents, blocages, priorites.
- Mensuel gouvernance (60 min): KPI, arbitrages budget/delais, decisions.
- Trimestriel direction (45 min): trajectoire globale, risques residuels, investissements.

Cette gouvernance est volontairement legere. Elle tient dans les agendas et produit des decisions actionnables.

## Cas pratique: ETI multisites en acceleration

Une ETI de services avait des pratiques cyber heterogenes sur six sites. Aucun incident majeur recent, mais plusieurs signaux faibles: phishing reussi, droits admin trop larges, tests de restauration rares. En 12 mois, l'entreprise a deploye une feuille de route en trois vagues.

0-3 mois: MFA admin, inventaire des actifs critiques, revue comptes dormant, premier test de restauration. 3-6 mois: comite cyber, politique SSI, processus vulnerabilites, onboarding fournisseurs critiques. 6-12 mois: exercices de crise, journalisation centralisee, tableaux de bord trimestriels direction.

Resultat: baisse des incidents a impact, reduction du delai de remediation, et meilleure confiance des clients lors des revues de securite pre-contractuelles. Le principal facteur de succes a ete la discipline de pilotage, pas la multiplication d'outils.

## FAQ rapide

### Faut-il commencer par acheter des outils ?

Pas en premier. Commencez par la priorisation des risques et la gouvernance. Les outils viennent ensuite, au service d'un plan clair.

### Une PME peut-elle avoir une feuille de route cyber "serieuse" ?

Oui. La qualite du pilotage compte plus que la taille de l'entreprise.

### Quel est le premier indicateur a suivre ?

Le taux de completion des actions prioritaires assorti d'un niveau de risque residuel.

## Pour aller plus loin

Prolongez cette lecture avec notre page [Cyber securite](/cyber-securite), puis connectez vos chantiers a [AMOA SI](/amoa-si) pour accelerer l'execution. Pour articuler vos priorites selon votre type d'organisation, consultez [Public, PME et ETI](/public-pme-eti). Vous pouvez aussi nous contacter directement via [Contact OLING](/contact).

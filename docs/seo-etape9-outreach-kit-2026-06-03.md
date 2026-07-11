# Etape 9 SEO - Kit outreach backlinks (mails + relances + QA)

Date: 3 juin 2026

## 1) Objectif de l'etape

Industrialiser l'acquisition de liens locaux qualifies sans degradation de qualite:
- contact initial standardise par type de prospect;
- relances cadencees (J+4, J+10, J+21);
- validation stricte avant passage `status=live`.

Perimetre prioritaire:
- Pages expertises: `/amoa-si`, `/si-finance`, `/facturation-electronique-amoa`, `/infrastructure-si-amoa`, `/conformite-reglementaire`.
- Pages zones: Paris, Lyon, Toulouse, Montpellier, Nantes, Bordeaux, Guadeloupe, Martinique, La Reunion, Guyane, Saint-Pierre-et-Miquelon.

## 2) Templates de mails outreach

Regles communes:
- 1 objectif par mail.
- 120 a 170 mots max.
- CTA binaire: "ok pour publier ce lien?".
- Personnalisation minimale obligatoire: `{prenom}`, `{structure}`, `{zone}`, `{expertise}`.

### Template T1 - Citation / annuaire local

Objet SEO:
- `Mise a jour fiche {structure} - lien local {zone}`

Corps:
```
Bonjour {prenom},

Je vous contacte pour mettre a jour notre presence locale sur {structure}.
Nous intervenons en conseil AMOA SI et conformite, notamment sur {zone}.

Pouvez-vous ajouter (ou corriger) ce lien sur notre fiche:
{url_cible_zone}

Libelle recommande:
OLING Management et Technologie

Si vous preferez, je peux vous envoyer un bloc NAP complet (nom/adresse/site) au format de votre annuaire.

Merci d'avance,
{signature}
```

### Template T2 - Partenaire / ecosysteme / cluster

Objet SEO:
- `Proposition lien partenaire - {zone} - {expertise}`

Corps:
```
Bonjour {prenom},

Nous accompagnons des organisations sur {expertise} dans la region {zone}.
Dans le cadre de votre page partenaires / ressources, je vous propose un lien contextuel vers:
{url_cible_expertise}

Suggestion d'ancre:
{ancre_recommandee}

En retour, nous pouvons mentionner votre structure dans notre bloc ecosysteme local.
Souhaitez-vous que je vous envoie un texte court (2-3 lignes) pret a publier?

Bien a vous,
{signature}
```

### Template T3 - Media local / tribune

Objet SEO:
- `Tribune experte {theme} - angle local {zone}`

Corps:
```
Bonjour {prenom},

Je vous propose une contribution courte sur {theme} avec cas concrets terrain:
- AMOA SI et pilotage programme
- SI finance / facturation electronique
- conformite IA Act, ISO, cyber

Si le sujet vous convient, nous ajoutons 1 lien de contexte vers:
{url_cible_expertise_ou_zone}

Je peux envoyer un pitch de 10 lignes aujourd'hui.
Partant pour validation editoriale?

Cordialement,
{signature}
```

### Template T4 - Association / evenement / webinar

Objet SEO:
- `Compte-rendu intervention + lien ressource {zone}`

Corps:
```
Bonjour {prenom},

Suite a notre intervention sur {evenement}, pouvez-vous ajouter un lien ressource dans la page recap:
{url_cible_expertise}

Texte suggere:
{ancre_recommandee}

Nous pouvons aussi fournir un resume "3 enseignements operationnels" pour enrichir la page.

Merci pour votre retour,
{signature}
```

## 3) Scripts de relance

Cadence recommandee:
- Relance 1: J+4
- Relance 2: J+10
- Relance 3: J+21 (dernier contact)

### Relance R1 (J+4) - Email court
```
Bonjour {prenom},

Je me permets une relance rapide sur ma demande de lien vers:
{url_cible}

Si c'est OK pour vous, je vous envoie le texte exact a copier-coller.

Merci,
{signature}
```

### Relance R2 (J+10) - Valeur ajoutee
```
Bonjour {prenom},

Pour faciliter la publication, voici une version prete a l'emploi (2 lignes):
{bloc_pret_a_publier}

Lien cible:
{url_cible}

Dites-moi simplement "OK" et je vous confirme la version finale.

Bien a vous,
{signature}
```

### Relance R3 (J+21) - Cloture propre
```
Bonjour {prenom},

Derniere relance de mon cote pour ne pas surcharger votre boite.
Si le lien vers {url_cible} n'est pas prioritaire, je cloture la demande.

Si besoin, je reste disponible plus tard.

Cordialement,
{signature}
```

### Script appel 30 secondes (option)
```
Bonjour {prenom}, {prenom_agent} a l'appareil.
Je vous appelle suite a mon email concernant une mise a jour de lien sur votre page {structure}.
L'objectif est juste d'ajouter un lien ressource vers {url_cible}, deja pret.
Si vous etes d'accord, je vous renvoie le texte final tout de suite.
```

## 4) Checklist qualite avant validation lien live

Statut autorise `live` uniquement si tous les points BLOQUANTS sont valides.

### A. Bloquants (obligatoires)
- [ ] URL source du lien repond `HTTP 200`.
- [ ] Page source indexable (`meta robots` sans `noindex`).
- [ ] Lien pointe vers la bonne URL cible Oling (pas de 404, pas de redirection casse).
- [ ] Ancre conforme au plan (brand/semi/exact selon mix).
- [ ] Contexte editorial coherent (SI, AMOA, conformite, transformation, secteur cible).
- [ ] Domaine non spam (pas de page surchargee de liens artificiels).

### B. Non bloquants (qualite +)
- [ ] Lien en `dofollow` (si nofollow, garder mais tagger).
- [ ] Position du lien visible dans le corps de page (pas uniquement footer global).
- [ ] Mention de zone ou d'expertise coherente avec la landing cible.
- [ ] Date de publication renseignee.
- [ ] Capture d'ecran archivee (preuve de mise en ligne).

### C. Scoring QA rapide (0-10)
- Indexabilite: /2
- Pertinence thematique: /2
- Pertinence geographique: /2
- Qualite domaine/source: /2
- Qualite ancre + destination: /2

Regle:
- `qa_score >= 7`: `qa_status=approved`
- `qa_score 5-6`: `qa_status=monitor`
- `qa_score < 5`: `qa_status=reject`

## 5) Pipeline operatoire

Le suivi execution est dans:
- `/Users/florestanrouet/myweb/olingw/output/spreadsheet/seo_outreach_step9_pipeline.csv`

Cycle standard par ligne:
- `todo` -> `contacted` -> `followup1` -> `followup2` -> `followup3` -> `live` -> `qa_approved` (ou `qa_reject`)

## 6) Definition de fini (DoD etape 9)

Etape 9 est terminee quand:
- 100% des opportunites P1 ont recu un contact initial.
- >=80% des contacts ont suivi la cadence de relance complete.
- 100% des liens `live` ont une evaluation QA renseignee.
- les liens `qa_reject` sont remplaces par une nouvelle opportunite dans les 7 jours.

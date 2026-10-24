# Qualification — métadonnées natives inconnues

Base distante : PR32 / `69a1b597`, travaux CRM intégrés et préservés. Mission existante contrôlée après interruption : `0c1365ed78526eadbef15420d21f4fc3`, owner2, requested/version2, aucun run. Aucun doublon créé.

Choix : marker réservé `unknown` dans les champs string existants NOT NULL. Aucune migration ; aucune modification des modèles/efforts configurés dans Codex. Qualité dérivée operator_report / not_independently_verified et reported/not_provided. Affichage français « non renseigné ».

Tests : AI29tests/331assertions, CRM55tests/712assertions. Les cas connus, mixtes, inconnus et invalides sont couverts, ainsi que le rejeu sans nouveau run/asset, la propriété et le rendu HTTP connu/inconnu. Une revue synthétique ne clôture pas automatiquement. Conteneur,6Twig et syntaxe PHP valides. Les tests sont locaux et synthétiques, distincts de la mission réelle.

Publication bornée : trois fichiers applicatifs, empreintes antérieures vérifiées ; sauvegarde ciblée et copie de la release réellement active, sous le verrou partagé. Conservation de tous les autres fichiers, y compris public/crm, du stockage privé et des données. Aucune migration, configuration, clé, service, OAuth ou DNS. Le helper `deploy-metadata.py` exige le nom de release observé en préflight ; il refuse un changement concurrent.

Suite réelle autorisée : reprendre exclusivement la mission existante, associer avec model=unknown/effort=unknown et la tâche native réelle, transmettre started puis le résultat réel, rejouer ce résultat identique et relire. État final attendu result_received ; un run, un asset, aucune revue/clôture. Les preuves finales sont consignées séparément après exécution.

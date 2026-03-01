<?php

namespace App\Service;

final class LegalPageDefaults
{
    public static function defaults(): array
    {
        return [
            'polrgpd' => [
                'title' => 'Charte de Protection des Données à Caractère Personnel',
                'body' => <<<HTML
<div class="mb-7">
  <p>La société OLING MANAGEMENT & TECHNOLOGIE est consciente des enjeux de la protection des données personnelles sur son site internet. À ce titre, elle s’engage à assurer un niveau de protection des données personnelles en conformité avec le Règlement (UE) 2016/679 du Parlement européen et du Conseil du 27 avril 2016 relatif à la protection des personnes physiques à l’égard du traitement des données à caractère personnel et à la libre circulation de ces données (RGPD) applicable depuis le 25 mai 2018 ainsi que la loi n°78-17 du 6 janvier 1978 relative à l’informatique, aux fichiers et aux libertés modifiée par la loi n°2018-493 du 20 juin 2018 promulguée le 21 juin 2018.</p>
  <p>Pour toute information complémentaire sur la protection des données personnelles en France et en Europe, vous pouvez consulter le site de la Commission Nationale Informatique et Libertés (CNIL) en vous rendant à la page suivante : <a href="https://www.cnil.fr">https://www.cnil.fr</a>.</p>
</div>
<div class="mb-7">
  <h4>Quelques définitions</h4>
  <ul>
    <li><strong>Donnée à caractère personnel :</strong> désigne toute information permettant d’identifier directement ou indirectement une personne physique. Il peut s’agir d’un nom, d’un numéro de téléphone, d’une adresse postale, d’un mail, etc.</li>
    <li><strong>Traitement de données à caractère personnel :</strong> désigne toute opération ou ensemble d’opérations effectuées, automatisées ou non, sur des données personnelles. Exemple : collecte, enregistrement, conservation.</li>
    <li><strong>Responsable de traitement :</strong> toute personne physique ou morale qui détermine les finalités et les moyens du traitement.</li>
    <li><strong>Sous-traitant :</strong> toute personne qui traite des données pour le compte du responsable de traitement.</li>
    <li><strong>Destinataire :</strong> toute personne qui reçoit des données personnelles.</li>
  </ul>
</div>
<div class="mb-7">
  <h4>Responsable de traitement des données personnelles</h4>
  <p>Le responsable de traitement est OLING MANAGEMENT & TECHNOLOGIE.</p>
  <ul>
    <li>Par courrier : OLING - rue René RABAT - 97122 Baie-Mahault</li>
    <li>Par mail : <a href="mailto:dpo@oling.fr">dpo@oling.fr</a></li>
  </ul>
</div>
<div class="mb-7">
  <h4>Hébergement des données</h4>
  <ul>
    <li>Identité de l’hébergeur : PlanetCloud SA</li>
    <li>Adresse : 4416 Louis-B.-Mayer, Laval, QC H7P 0G1, Canada</li>
  </ul>
</div>
<div class="mb-7">
  <h4>Données personnelles collectées et finalités</h4>
  <p>Les formulaires disponibles sur notre site collectent les données suivantes :</p>
  <ul>
    <li>Nom</li>
    <li>Prénom</li>
    <li>Société</li>
    <li>Adresse mail</li>
    <li>Numéro de téléphone</li>
  </ul>
  <p>OLING MANAGEMENT & TECHNOLOGIE collecte vos données pour gérer vos demandes. Les données sont traitées uniquement pour des finalités légitimes et spécifiques.</p>
</div>
<div class="mb-7">
  <h4>Conservation des données personnelles</h4>
  <p>OLING MANAGEMENT & TECHNOLOGIE ASM se doit de conserver certaines données personnelles pour un bon suivi. La durée de conservation dépendra du besoin et de la finalité du traitement. Nous nous engageons à ne pas conserver vos données indéfiniment.</p>
</div>
<div class="mb-7">
  <h4>Destinataires des données personnelles</h4>
  <p>Les données collectées sont destinées au personnel traitant les demandes. Dans certains cas, des données peuvent être transférées à des tiers si nécessaire. Aucune donnée ne sera transférée hors de l'Union Européenne sans votre consentement.</p>
</div>
<div class="mb-7">
  <h4>Base juridique du traitement</h4>
  <p>Nous traitons les données personnelles uniquement si nous avons une base juridique valable, comme le consentement, l’exécution d’un contrat, une obligation légale ou la poursuite d’intérêts légitimes.</p>
</div>
<div class="mb-7">
  <h4>Vos droits</h4>
  <ul>
    <li>Droit d’accès à vos données</li>
    <li>Droit de rectification</li>
    <li>Droit à l’effacement</li>
    <li>Droit à la portabilité</li>
    <li>Droit d’opposition</li>
    <li>Droit à la limitation du traitement</li>
    <li>Droit de retrait du consentement</li>
    <li>Droit d’introduire une réclamation auprès de la CNIL</li>
  </ul>
  <p>Vous pouvez exercer vos droits en nous contactant. Une réponse vous sera apportée dans les 30 jours.</p>
</div>
<div class="mb-7">
  <h4>Mesures pour la protection de vos données</h4>
  <p>Des mesures organisationnelles et techniques sont mises en place pour garantir la sécurité et l’intégrité de vos données. Nos équipes s’engagent à respecter la confidentialité des données personnelles traitées.</p>
</div>
<div class="mb-7">
  <h4>Mise à jour de la charte</h4>
  <p>Nous nous réservons le droit de modifier cette charte à tout moment. Toute modification sera publiée sur notre site web et prendra effet immédiatement.</p>
</div>
HTML,
            ],
            'polsecurite' => [
                'title' => "Politique de Sécurité de l'Information",
                'body' => <<<HTML
<div class="mb-7">
  <p>Depuis plus de 10 ans, nous nous engageons à fournir des services de qualité tout en établissant un climat de confiance. Cette politique dédiée à la sécurité de l’information vise à définir les principes, règles et engagements de sécurité de l'information au sein de <strong>OLING Management et Technologie</strong>.</p>
</div>
<div class="mb-7">
  <p>Son objectif est de protéger les données contre toutes les menaces, qu'elles soient internes ou externes, intentionnelles ou accidentelles afin de garantir la <strong>confidentialité</strong>, l’<strong>intégrité</strong> et la <strong>disponibilité</strong> des données. Cela nous permet d'assurer la continuité des opérations, de réduire les dommages et d'optimiser nos investissements et opportunités.</p>
</div>
<div class="mb-7">
  <p>Pour renforcer et objectiver ces notions, notre système de management de la sécurité de l’information est basé sur la norme <strong>ISO/CEI 27001:2022</strong> et couvre les domaines suivants :</p>
  <ul>
    <li>Services d'Assistance à Maîtrise d'Ouvrage en Système d'Information et organisation</li>
    <li>Services de Délégué à la Protection des Données et projets RGPD</li>
  </ul>
</div>
<div class="mb-7">
  <p>La sécurité des informations est essentielle pour notre entreprise et repose sur la formation et l’implication de nos équipes :</p>
  <ul>
    <li>Dans les questions organisationnelles, comportementales et techniques</li>
    <li>Dans le suivi permanent des actions et incidents</li>
    <li>Dans l’établissement d’une méthodologie structurée, coordonnée, ainsi que de règles et procédures à mettre en œuvre</li>
  </ul>
</div>
<div class="mb-7">
  <p>Ainsi, nous nous engageons auprès des membres de notre écosystème (utilisateurs, clients, salariés, partenaires, actionnaires, sous-traitants) à :</p>
  <ul>
    <li>Respecter les lois et réglementations, en particulier en matière de protection des données personnelles</li>
    <li>Identifier et gérer les risques liés à la sécurité de l'information</li>
    <li>Suivre et appliquer les meilleures pratiques de sécurité de l'information tout au long de nos projets</li>
    <li>Améliorer continuellement notre système de management de la sécurité de l'information</li>
  </ul>
</div>
<div class="mb-7">
  <p>Notre politique de sécurité est révisée chaque année ou en cas de changements significatifs, afin de garantir sa pertinence et son efficacité.</p>
</div>
<div class="mb-7">
  <p><em>Fait le 15 septembre 2025 à Baie Mahault</em></p>
  <p><strong>Florestan Rouet</strong></p>
</div>
HTML,
            ],
            'mentions-legales' => [
                'title' => 'Mentions légales et conditions générales du site oling.fr',
                'body' => <<<HTML
<div class="mb-7">
  <h4>Éditeur du site</h4>
  <p><strong>OLING Management et Technologie</strong><br>40 rue Alexandre DUMAS 75011, Paris, FRANCE<br>
  Téléphone : 01 89 70 15 60<br>
  Email : contact@oling.fr</p>
  <p>Société par actions simplifiée au capital de 8000 euros.<br>
  Numéro d'immatriculation RCS : 792 014 482<br>
  Numéro de TVA intracommunautaire : FR 52911666121 </p>
  <p>Directeur de la publication : Monsieur Florestan ROUET</p>
</div>
<div class="mb-7">
  <h4>Propriété intellectuelle</h4>
  <p>L'ensemble des éléments constituant le site oling.fr (textes, images, logos, vidéos, icônes, mise en page, charte graphique, base de données, etc.) est la propriété exclusive d'OLING ou de ses partenaires, et est protégé par les lois françaises et internationales relatives aux droits d'auteur et à la propriété intellectuelle. Toute reproduction, représentation, modification, publication, adaptation ou exploitation de tout ou partie des éléments du site, quel que soit le moyen ou le procédé utilisé, est interdite sans l'autorisation écrite préalable d'OLING.</p>
</div>
<div class="mb-7">
  <h4>Données personnelles</h4>
  <p>OLING s'engage à protéger la vie privée des utilisateurs de son site et à respecter les dispositions légales en vigueur relatives à la protection des données personnelles. Les données personnelles recueillies sur le site oling.fr sont traitées conformément à notre politique de confidentialité, accessible à l'adresse suivante :
  <a class="link" href="/a-propos/politiquergpd">politique générale RGPD OLING</a></p>
</div>
<div class="mb-7">
  <h4>Limitation de responsabilité</h4>
  <p>OLING ne saurait être tenu responsable des dommages directs ou indirects résultant de l'accès ou de l'utilisation du site oling.fr, y compris l'inaccessibilité, les pertes de données, détériorations, destructions ou virus qui pourraient affecter l'équipement informatique de l'utilisateur. OLING se réserve le droit de modifier, suspendre ou interrompre la diffusion de tout ou partie du site oling.fr à tout moment et sans préavis.</p>
</div>
<div class="mb-7">
  <h4>Liens hypertextes</h4>
  <p>Le site oling.fr peut contenir des liens vers d'autres sites internet. OLING n'est pas responsable des contenus, de la disponibilité, du fonctionnement ou des éventuels dommages causés par ces sites.</p>
</div>
<div class="mb-7">
  <h4>Loi applicable</h4>
  <p>Les présentes mentions légales sont soumises au droit français. En cas de litige, les tribunaux français seront seuls compétents.</p>
  <p>Dernière mise à jour le jeudi 13 avril 2023</p>
  <p>Note : Ces mentions légales sont données à titre d'exemple et peuvent nécessiter des modifications pour s'adapter à la situation spécifique d'OLING. Il est recommandé de consulter un conseiller juridique pour adapter ces mentions légales à la réalité de l'entreprise.</p>
</div>
HTML,
            ],
        ];
    }
}

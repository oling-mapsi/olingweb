<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261003110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Normalize public OLING team source of truth for FR EN ES';
    }

    public function up(Schema $schema): void
    {
        $profilesByLocale = $this->profilesByLocale();
        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');

        $teams = $this->connection->fetchAllAssociative('SELECT id, noncomplet FROM team');
        $teamsByName = [];
        foreach ($teams as $team) {
            $normalizedName = $this->normalize((string) $team['noncomplet']);
            $teamsByName[$normalizedName] = (int) $team['id'];
            if ($normalizedName === 'claire tillon') {
                $teamsByName['claire tillion'] = (int) $team['id'];
            }
        }

        foreach ($profilesByLocale['fr'] as $index => $profile) {
            $key = $this->normalize((string) $profile['displayName']);
            $teamId = $teamsByName[$key] ?? null;

            if ($teamId === null) {
                $this->connection->insert('team', [
                    'noncomplet' => $profile['displayName'],
                    'photo' => $profile['photo'],
                    'shortcv' => $profile['shortcv'] ?: null,
                    'linkedin' => $profile['linkedin'],
                    'titre' => null,
                ]);
                $teamId = (int) $this->connection->lastInsertId();
                $teamsByName[$key] = $teamId;
            } else {
                $this->connection->update('team', [
                    'noncomplet' => $profile['displayName'],
                    'photo' => $profile['photo'],
                    'shortcv' => $profile['shortcv'] ?: null,
                    'linkedin' => $profile['linkedin'],
                    'titre' => null,
                ], ['id' => $teamId]);
            }

            foreach ($profilesByLocale as $locale => $localizedProfiles) {
                $this->upsertTranslation($teamId, $locale, $localizedProfiles[$index], $now);
            }
        }

        $gilbertId = $teamsByName['gilbert rinaldo'] ?? null;
        if ($gilbertId !== null) {
            $this->connection->executeStatement(
                'UPDATE team_translation SET public_profile = NULL, updated_at = :updatedAt WHERE team_id = :teamId',
                ['teamId' => $gilbertId, 'updatedAt' => $now]
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE team_translation SET public_profile = NULL WHERE JSON_UNQUOTE(JSON_EXTRACT(public_profile, "$.displayName")) = "Claire Tillion"');
    }

    /**
     * @param array<string, mixed> $profile
     */
    private function upsertTranslation(int $teamId, string $locale, array $profile, string $now): void
    {
        $json = json_encode($profile, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $hash = hash('sha256', $json);
        $this->connection->executeStatement(
            'INSERT INTO team_translation (team_id, locale, titre, shortcv, public_profile, translation_status, source_content_hash, source_updated_at, created_at, updated_at)
             VALUES (:teamId, :locale, NULL, :shortcv, :publicProfile, "published", :hash, :sourceUpdatedAt, :createdAt, :updatedAt)
             ON DUPLICATE KEY UPDATE titre = VALUES(titre), shortcv = VALUES(shortcv), public_profile = VALUES(public_profile), translation_status = VALUES(translation_status), source_content_hash = VALUES(source_content_hash), source_updated_at = VALUES(source_updated_at), updated_at = VALUES(updated_at)',
            [
                'teamId' => $teamId,
                'locale' => $locale,
                'shortcv' => $profile['shortcv'] !== '' ? $profile['shortcv'] : null,
                'publicProfile' => $json,
                'hash' => $hash,
                'sourceUpdatedAt' => $now,
                'createdAt' => $now,
                'updatedAt' => $now,
            ]
        );
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function profilesByLocale(): array
    {
        $fr = [
            [
                'name' => 'florestan rouet',
                'slug' => 'florestan-rouet',
                'displayName' => 'Florestan Rouet',
                'photo' => '/img/people/florestan-oling.png',
                'shortcv' => 'Florestan Rouet accompagne les organisations dans le cadrage et la gouvernance de leurs projets SI. Il publie également sur la GRC et le pilotage avec MAPSI.',
                'areas' => [['label' => 'AMOA SI', 'href' => '/amoa-si'], ['label' => 'Gouvernance SI'], ['label' => 'GRC / MAPSI']],
                'linkedin' => 'https://www.linkedin.com/in/florestanrouet/',
                'publicationsUrl' => 'https://mapsi.fr/fr/auteurs/florestan-rouet',
                'relationSchema' => 'worksFor',
                'relationshipText' => 'OLING Management et Technologie',
            ],
            [
                'name' => 'dorothee maitrias',
                'slug' => 'dorothee-maitrias',
                'displayName' => 'Dorothée Maitrias',
                'photo' => '/img/people/dorothee-oling.jpg',
                'shortcv' => 'Dorothée Maitrias accompagne les démarches qualité, QSE et d’amélioration continue, de leur structuration à leur pilotage opérationnel.',
                'areas' => [['label' => 'Qualité / QSE', 'href' => '/expertises-audit/qse'], ['label' => 'Systèmes de management']],
                'linkedin' => 'https://www.linkedin.com/in/dorothee-maitrias-0584b196/',
                'relationSchema' => 'affiliation',
                'relationshipText' => 'Intervient avec OLING Management et Technologie',
            ],
            [
                'name' => 'manuel feuillard',
                'slug' => 'manuel-feuillard',
                'displayName' => 'Manuel Feuillard',
                'photo' => '/img/people/manuel-oling.png',
                'shortcv' => 'Manuel Feuillard intervient sur les systèmes de management QSE et leur digitalisation, avec une approche adaptée aux processus et au contexte de chaque organisation.',
                'areas' => [['label' => 'QSE', 'href' => '/expertises-audit/qse'], ['label' => 'Systèmes de management'], ['label' => 'Digitalisation']],
                'linkedin' => 'https://www.linkedin.com/in/manuel-feuillard-a10b84248/',
                'relationSchema' => 'affiliation',
                'relationshipText' => 'Intervient avec OLING Management et Technologie',
            ],
            [
                'name' => 'hanna badan',
                'slug' => 'hanna-badan',
                'displayName' => 'Hanna Badan',
                'photo' => '/img/people/hanna-oling.jpg',
                'shortcv' => 'Hanna Badan contribue au cadrage des transformations SI et des projets ERP ou progiciels, en reliant besoins métier, exploitation et pilotage.',
                'areas' => [['label' => 'ERP et progiciels', 'href' => '/business-apps/erp'], ['label' => 'Transformation SI']],
                'linkedin' => 'https://www.linkedin.com/in/hannabadan/',
                'relationSchema' => 'affiliation',
                'relationshipText' => 'Intervient avec OLING Management et Technologie',
            ],
            [
                'name' => 'julien pujol',
                'slug' => 'julien-pujol',
                'displayName' => 'Julien Pujol',
                'photo' => '/img/people/julien-oling.png',
                'shortcv' => 'Julien Pujol intervient sur l’intégration d’ERP et les enjeux associés de finance et de décisionnel.',
                'areas' => [['label' => 'ERP', 'href' => '/business-apps/erp'], ['label' => 'SI Finance', 'href' => '/si-finance'], ['label' => 'BI']],
                'linkedin' => 'https://www.linkedin.com/in/julien-pujol-752a364/',
                'relationSchema' => 'affiliation',
                'relationshipText' => 'Intervient avec OLING Management et Technologie',
            ],
            [
                'name' => 'claire tillion',
                'slug' => 'claire-tillion',
                'displayName' => 'Claire Tillion',
                'photo' => '/img/people/claire-oling.png',
                'shortcv' => '',
                'areas' => [],
                'linkedin' => null,
                'relationSchema' => 'affiliation',
                'relationshipText' => 'Intervient avec OLING Management et Technologie',
            ],
            [
                'name' => 'jean claude vati',
                'slug' => 'jean-claude-vati',
                'displayName' => 'Jean-Claude Vati',
                'photo' => 'https://oling.fr/uploads/teams/photos/1767729618943-69d0310ed53909.71845746.jpg',
                'shortcv' => 'Jean-Claude Vati accompagne les projets d’infrastructure SI et l’évolution des environnements Microsoft 365.',
                'areas' => [['label' => 'Infrastructure SI', 'href' => '/infrastructure-si-amoa'], ['label' => 'Microsoft 365']],
                'linkedin' => 'https://www.linkedin.com/in/jean-claude-vati-415ba33a3/',
                'relationSchema' => 'affiliation',
                'relationshipText' => 'Intervient avec OLING Management et Technologie',
            ],
        ];

        $en = $fr;
        $es = $fr;
        $en[0]['shortcv'] = 'Florestan Rouet supports organisations in scoping and governing their IT projects. He also publishes insights on GRC and management with MAPSI.';
        $en[0]['areas'] = [['label' => 'IT Project Advisory', 'href' => '/amoa-si'], ['label' => 'IT Governance'], ['label' => 'GRC / MAPSI']];
        $en[0]['relationshipText'] = 'OLING Management and Technology';
        $en[1]['shortcv'] = 'Dorothée Maitrias supports quality, QHSE and continuous improvement initiatives, from structuring them to overseeing their operational implementation.';
        $en[1]['areas'] = [['label' => 'Quality / QHSE', 'href' => '/expertises-audit/qse'], ['label' => 'Management systems']];
        $en[1]['relationshipText'] = 'Works with OLING Management and Technology';
        $en[2]['shortcv'] = 'Manuel Feuillard works on QHSE management systems and their digitalisation, tailoring his approach to each organisation’s processes and context.';
        $en[2]['areas'] = [['label' => 'QHSE', 'href' => '/expertises-audit/qse'], ['label' => 'Management systems'], ['label' => 'Digitalisation']];
        $en[2]['relationshipText'] = 'Works with OLING Management and Technology';
        $en[3]['shortcv'] = 'Hanna Badan contributes to scoping IT transformation initiatives and ERP or business software projects, connecting business needs, operations and management oversight.';
        $en[3]['areas'] = [['label' => 'ERP and business software', 'href' => '/business-apps/erp'], ['label' => 'IT transformation']];
        $en[3]['relationshipText'] = 'Works with OLING Management and Technology';
        $en[4]['shortcv'] = 'Julien Pujol advises on ERP integration and related finance and business intelligence challenges.';
        $en[4]['areas'] = [['label' => 'ERP', 'href' => '/business-apps/erp'], ['label' => 'Finance Information Systems', 'href' => '/si-finance'], ['label' => 'BI']];
        $en[4]['relationshipText'] = 'Works with OLING Management and Technology';
        $en[5]['relationshipText'] = 'Works with OLING Management and Technology';
        $en[6]['shortcv'] = 'Jean-Claude Vati supports IT infrastructure projects and the development of Microsoft 365 environments.';
        $en[6]['areas'] = [['label' => 'IT Infrastructure', 'href' => '/infrastructure-si-amoa'], ['label' => 'Microsoft 365']];
        $en[6]['relationshipText'] = 'Works with OLING Management and Technology';

        $es[0]['shortcv'] = 'Florestan Rouet acompaña a las organizaciones en la definición del alcance y la gobernanza de sus proyectos de sistemas de información. También publica contenidos sobre GRC y gestión con MAPSI.';
        $es[0]['areas'] = [['label' => 'Asesoría en proyectos de sistemas de información', 'href' => '/amoa-si'], ['label' => 'Gobernanza de sistemas de información'], ['label' => 'GRC / MAPSI']];
        $es[1]['shortcv'] = 'Dorothée Maitrias acompaña iniciativas de calidad, QSE y mejora continua, desde su estructuración hasta su gestión operativa.';
        $es[1]['areas'] = [['label' => 'Calidad / QSE', 'href' => '/expertises-audit/qse'], ['label' => 'Sistemas de gestión']];
        $es[1]['relationshipText'] = 'Colabora con OLING Management et Technologie';
        $es[2]['shortcv'] = 'Manuel Feuillard trabaja en sistemas de gestión de calidad, seguridad y medio ambiente (QSE) y en su digitalización, con un enfoque adaptado a los procesos y al contexto de cada organización.';
        $es[2]['areas'] = [['label' => 'QSE', 'href' => '/expertises-audit/qse'], ['label' => 'Sistemas de gestión'], ['label' => 'Digitalización']];
        $es[2]['relationshipText'] = 'Colabora con OLING Management et Technologie';
        $es[3]['shortcv'] = 'Hanna Badan contribuye a definir las transformaciones de los sistemas de información y los proyectos de ERP o software empresarial, conectando las necesidades del negocio, las operaciones y la gestión.';
        $es[3]['areas'] = [['label' => 'ERP y software empresarial', 'href' => '/business-apps/erp'], ['label' => 'Transformación de los sistemas de información']];
        $es[3]['relationshipText'] = 'Colabora con OLING Management et Technologie';
        $es[4]['shortcv'] = 'Julien Pujol trabaja en la integración de sistemas ERP y en los retos asociados de finanzas e inteligencia empresarial.';
        $es[4]['areas'] = [['label' => 'ERP', 'href' => '/business-apps/erp'], ['label' => 'Sistemas de información financieros', 'href' => '/si-finance'], ['label' => 'BI']];
        $es[4]['relationshipText'] = 'Colabora con OLING Management et Technologie';
        $es[5]['relationshipText'] = 'Colabora con OLING Management et Technologie';
        $es[6]['shortcv'] = 'Jean-Claude Vati acompaña proyectos de infraestructura de TI y la evolución de entornos Microsoft 365.';
        $es[6]['areas'] = [['label' => 'Infraestructura de TI', 'href' => '/infrastructure-si-amoa'], ['label' => 'Microsoft 365']];
        $es[6]['relationshipText'] = 'Colabora con OLING Management et Technologie';

        return ['fr' => $fr, 'en' => $en, 'es' => $es];
    }

    private function normalize(string $value): string
    {
        $value = strtr($value, [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', 'ã' => 'a', 'å' => 'a',
            'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ñ' => 'n',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y',
            'À' => 'a', 'Á' => 'a', 'Â' => 'a', 'Ä' => 'a', 'Ã' => 'a', 'Å' => 'a',
            'Ç' => 'c',
            'È' => 'e', 'É' => 'e', 'Ê' => 'e', 'Ë' => 'e',
            'Ì' => 'i', 'Í' => 'i', 'Î' => 'i', 'Ï' => 'i',
            'Ñ' => 'n',
            'Ò' => 'o', 'Ó' => 'o', 'Ô' => 'o', 'Ö' => 'o', 'Õ' => 'o',
            'Ù' => 'u', 'Ú' => 'u', 'Û' => 'u', 'Ü' => 'u',
            'Ý' => 'y',
        ]);

        return trim(preg_replace('/[^a-z0-9]+/', ' ', strtolower($value)) ?? '');
    }
}

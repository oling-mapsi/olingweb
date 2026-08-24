<?php

namespace App\Command;

use App\Entity\Projet;
use App\Repository\ProjetRepository;
use App\Service\SimpleXlsxReader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:projects:import-seo-referential', description: 'Importe le référentiel projets OLING depuis le XLSX 2026.')]
class ImportProjectsSeoReferentialCommand extends Command
{
    public function __construct(
        private readonly ProjetRepository $projetRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly SimpleXlsxReader $xlsxReader,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('path', InputArgument::REQUIRED, 'Chemin du fichier XLSX')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Analyse sans écrire en base');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = (string) $input->getArgument('path');
        $dryRun = (bool) $input->getOption('dry-run');

        if (!is_file($path)) {
            $io->error(sprintf('Fichier introuvable : %s', $path));
            return Command::FAILURE;
        }

        $created = 0;
        $updated = 0;
        [$createdProjects, $updatedProjects] = $this->importFeaturedProjects($path, $dryRun);
        $created += $createdProjects;
        $updated += $updatedProjects;

        [$createdActions, $updatedActions] = $this->importActions($path, $dryRun);
        $created += $createdActions;
        $updated += $updatedActions;

        if (!$dryRun) {
            $this->entityManager->flush();
        }

        $historicalCount = $this->projetRepository->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.externalId IS NULL')
            ->getQuery()
            ->getSingleScalarResult();

        $io->success(sprintf(
            'Import terminé%s : %d créé(s), %d mis à jour, %d référence(s) historique(s) conservée(s).',
            $dryRun ? ' (dry-run)' : '',
            $created,
            $updated,
            (int) $historicalCount
        ));

        return Command::SUCCESS;
    }

    private function importFeaturedProjects(string $path, bool $dryRun): array
    {
        $rows = $this->xlsxReader->readSheetRows($path, 'Projets_phares_25');
        if (count($rows) < 2) {
            throw new \RuntimeException('Feuille Projets_phares_25 vide.');
        }

        $headers = array_map('trim', $rows[0]);
        $created = 0;
        $updated = 0;

        foreach (array_slice($rows, 1) as $row) {
            $data = $this->combineRow($headers, $row);
            $externalId = $this->value($data, 'ID projet phare');
            $designation = $this->value($data, 'Désignation projet phare');

            if ($externalId === '' || $designation === '') {
                continue;
            }

            [$projet, $isNew] = $this->findOrCreateProject($externalId);
            $isNew ? ++$created : ++$updated;

            $order = (int) $this->value($data, 'Ordre');

            $projet
                ->setDesignation($designation)
                ->setClientName($this->value($data, 'Client / bénéficiaire') ?: null)
                ->setTerritory($this->value($data, 'Territoire') ?: null)
                ->setPeriodLabel($this->value($data, 'Période') ?: null)
                ->setPublicUrl($this->normalizeInternalUrl($this->value($data, 'URL projet cible')))
                ->setShortDescription($this->buildCardSummary($data))
                ->setDescription($this->value($data, 'Description longue') ?: $this->value($data, 'Description courte') ?: null)
                ->setProofStatus($this->value($data, 'Statut de la preuve') ?: null)
                ->setPublicationStatus($this->value($data, 'Publication nom client') ?: null)
                ->setSoftwareTags($this->splitList($this->value($data, 'Progiciels top 30 normalisés')))
                ->setSoftwareFamilies($this->splitList($this->value($data, 'Familles progicielles SEO')))
                ->setSoftwareRelation($this->value($data, 'Relation OLING / progiciel') ?: null)
                ->setSoftwarePriority($this->value($data, 'Priorité SEO progiciel') ?: null)
                ->setHistoricalReference(false)
                ->setFeaturedProjects($order > 0 && $order <= 6)
                ->setFeaturedProjectsRank($order > 0 && $order <= 6 ? $order : null)
                ->setMetadata($this->buildMetadata($data));

            if (!$dryRun) {
                $this->entityManager->persist($projet);
            }
        }

        return [$created, $updated];
    }

    private function importActions(string $path, bool $dryRun): array
    {
        $rows = $this->xlsxReader->readSheetRows($path, 'Actions_SEO_IA');
        if (count($rows) < 2) {
            throw new \RuntimeException('Feuille Actions_SEO_IA vide.');
        }

        $headers = array_map('trim', $rows[0]);
        $created = 0;
        $updated = 0;

        foreach (array_slice($rows, 1) as $row) {
            $data = $this->combineRow($headers, $row);
            $externalId = $this->value($data, 'ID action');
            $designation = $this->buildActionDesignation($data);

            if ($externalId === '' || $designation === '') {
                continue;
            }

            [$projet, $isNew] = $this->findOrCreateProject($externalId);
            $isNew ? ++$created : ++$updated;

            $projet
                ->setDesignation($designation)
                ->setClientName($this->value($data, 'Client / bénéficiaire') ?: null)
                ->setTerritory($this->value($data, 'Zone / territoire') ?: null)
                ->setPeriodLabel($this->buildActionPeriod($data))
                ->setPublicUrl($this->resolveActionPublicUrl($data))
                ->setShortDescription($this->buildCardSummary($data))
                ->setDescription($this->value($data, 'Description longue') ?: $this->value($data, 'Description courte') ?: null)
                ->setProofStatus($this->value($data, 'Statut de la preuve') ?: null)
                ->setPublicationStatus($this->value($data, 'Publication nom client') ?: null)
                ->setSoftwareTags($this->splitList($this->value($data, 'Progiciels top 30 normalisés')))
                ->setSoftwareFamilies($this->splitList($this->value($data, 'Familles progicielles SEO')))
                ->setSoftwareRelation($this->value($data, 'Relation OLING / progiciel') ?: null)
                ->setSoftwarePriority($this->value($data, 'Priorité SEO progiciel') ?: null)
                ->setHistoricalReference(false)
                ->setFeaturedProjects(false)
                ->setFeaturedProjectsRank(null)
                ->setMetadata($this->buildActionMetadata($data));

            if (!$dryRun) {
                $this->entityManager->persist($projet);
            }
        }

        return [$created, $updated];
    }

    private function findOrCreateProject(string $externalId): array
    {
        $projet = $this->projetRepository->findOneBy(['externalId' => $externalId]);
        $isNew = $projet === null;

        if ($isNew) {
            $projet = new Projet();
            $projet->setExternalId($externalId);
        }

        return [$projet, $isNew];
    }

    private function combineRow(array $headers, array $row): array
    {
        $data = [];
        foreach ($headers as $index => $header) {
            if ($header === '') {
                continue;
            }
            $data[$header] = trim((string) ($row[$index] ?? ''));
        }

        return $data;
    }

    private function value(array $data, string $key): string
    {
        return trim((string) ($data[$key] ?? ''));
    }

    private function splitList(string $value): array
    {
        if ($value === '') {
            return [];
        }

        $parts = preg_split('/\s*;\s*|\s*\|\s*/', $value) ?: [];

        return array_values(array_filter(array_map(static fn (string $item) => trim($item), $parts)));
    }

    private function normalizeInternalUrl(string $url): ?string
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }

        if (str_starts_with($url, 'https://oling.fr')) {
            $path = parse_url($url, PHP_URL_PATH);
            return is_string($path) && $path !== '' ? $path : null;
        }

        return $url;
    }

    private function buildMetadata(array $data): array
    {
        return [
            'practice' => $this->value($data, 'Practice OLING'),
            'sub_practice' => $this->value($data, 'Sous-practice'),
            'sector' => $this->value($data, 'Secteur'),
            'editorial_angle' => $this->value($data, 'Angle éditorial SEO / IA'),
            'pillar_page' => $this->value($data, 'Page pilier cible'),
            'priority_wave' => $this->value($data, 'Vague'),
            'source_actions' => $this->splitList($this->value($data, 'Actions source')),
            'project_codes' => $this->splitList($this->value($data, 'Code(s) projet')),
            'internal_links' => $this->splitList($this->value($data, 'Maillage interne prioritaire')),
            'cta' => $this->value($data, 'CTA recommandé'),
            'guardrail' => $this->value($data, 'Validation progiciel / garde-fou'),
            'proof' => $this->value($data, 'Preuve / résultat observable'),
            'source' => $this->value($data, 'Source principale'),
        ];
    }

    private function buildActionMetadata(array $data): array
    {
        return [
            'practice' => $this->value($data, 'Practice OLING'),
            'sub_practice' => $this->value($data, 'Sous-practice / type de mission'),
            'sector' => $this->value($data, 'Secteur'),
            'editorial_angle' => $this->value($data, 'Thématique SEO principale'),
            'pillar_page' => $this->value($data, 'Page pilier cible'),
            'priority_wave' => $this->value($data, 'Vague publication'),
            'project_codes' => $this->splitList($this->value($data, 'Code projet principal')),
            'project_aliases' => $this->splitList($this->value($data, 'Codes projet associés / alias')),
            'linked_featured_project' => $this->value($data, 'ID projet phare'),
            'content_type' => $this->value($data, 'Type de contenu cible'),
            'internal_links' => $this->splitList($this->value($data, 'Maillage interne prioritaire')),
            'cta' => $this->value($data, 'CTA recommandé'),
            'guardrail' => $this->value($data, 'Validation progiciel / garde-fou'),
            'proof' => $this->value($data, 'Preuve / résultat observable'),
            'source' => $this->value($data, 'Source principale'),
        ];
    }

    private function buildCardSummary(array $data): ?string
    {
        $angle = $this->normalizeSentence($this->value($data, 'Angle éditorial SEO / IA'));
        if ($angle !== null) {
            return $angle;
        }

        $description = $this->value($data, 'Description courte');
        if ($description === '') {
            return null;
        }

        $cleaned = preg_replace('/\s+[A-Z][A-Z0-9 \/\-]{2,}[^.]*$/u', '', $description) ?: $description;
        $sentence = preg_split('/(?<=[.!?])\s+/u', trim($cleaned)) ?: [];

        return $this->normalizeSentence($sentence[0] ?? $cleaned);
    }

    private function normalizeSentence(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $value = preg_replace('/\s+/u', ' ', $value) ?: $value;
        $value = trim($value, " \t\n\r\0\x0B.;:");

        if ($value === '') {
            return null;
        }

        return str_ends_with($value, '.') ? $value : $value.'.';
    }

    private function buildActionDesignation(array $data): string
    {
        $designation = $this->value($data, 'Désignation SEO / H1');
        if ($designation !== '') {
            return $designation;
        }

        $source = $this->value($data, 'Action / intitulé source');
        if ($source !== '') {
            return $source;
        }

        return $this->value($data, 'Description courte');
    }

    private function buildActionPeriod(array $data): ?string
    {
        $start = $this->value($data, 'Date début action');
        $end = $this->value($data, 'Date fin / horizon');
        if ($start === '' && $end === '') {
            return null;
        }

        if ($start !== '' && $end !== '') {
            return $start.' → '.$end;
        }

        return $start !== '' ? $start : $end;
    }

    private function resolveActionPublicUrl(array $data): ?string
    {
        $projectUrl = $this->normalizeInternalUrl($this->value($data, 'URL projet cible'));
        if ($projectUrl !== null) {
            return $projectUrl;
        }

        $pillar = $this->normalizeInternalUrl($this->value($data, 'Page pilier cible'));
        if ($pillar !== null) {
            return $pillar;
        }

        return null;
    }
}

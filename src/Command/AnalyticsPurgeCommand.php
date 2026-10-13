<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:analytics:purge', description: 'Purge les événements analytics bruts anciens.')]
class AnalyticsPurgeCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('days', null, InputOption::VALUE_REQUIRED, 'Durée de conservation en jours', 90)
            ->addOption('event-id-prefix', null, InputOption::VALUE_REQUIRED, 'Limiter la purge aux event_id commençant par ce préfixe');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $days = max(1, (int) $input->getOption('days'));
        $prefix = trim((string) $input->getOption('event-id-prefix'));
        $threshold = (new \DateTimeImmutable())->modify(sprintf('-%d days', $days));
        $query = $this->entityManager->createQueryBuilder()
            ->delete('App\Entity\AnalyticsEvent', 'e')
            ->where('e.occurredAt < :threshold')
            ->setParameter('threshold', $threshold);

        if ($prefix !== '') {
            $query
                ->andWhere('e.eventId LIKE :prefix')
                ->setParameter('prefix', $prefix.'%');
        }

        $deleted = $query->getQuery()->execute();

        $output->writeln(sprintf('Événements supprimés : %d', $deleted));

        return Command::SUCCESS;
    }
}

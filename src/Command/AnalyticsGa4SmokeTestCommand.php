<?php

namespace App\Command;

use App\Service\Analytics\Ga4DataApiClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:analytics:ga4:smoke-test', description: 'Teste la lecture GA4 Data API sans afficher de secret.')]
class AnalyticsGa4SmokeTestCommand extends Command
{
    public function __construct(private readonly Ga4DataApiClient $ga4)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $status = $this->ga4->status();
        if (empty($status['configured'])) {
            $output->writeln('GA4 non configuré : définir GA4_PROPERTY_ID et GA4_CREDENTIALS_PATH vers un JSON de compte de service lisible.');
            return Command::FAILURE;
        }

        foreach ([7, 30] as $days) {
            $to = new \DateTimeImmutable('tomorrow');
            $from = $to->modify(sprintf('-%d days', $days));
            $summary = $this->ga4->summary($from, $to);
            if (!empty($summary['error'])) {
                $output->writeln(sprintf('GA4 %d jours : erreur %s', $days, $summary['error']));
                return Command::FAILURE;
            }

            $sessions = (int) ($summary['totals']['rows'][0]['metricValues'][0]['value'] ?? 0);
            $pageViews = (int) ($summary['totals']['rows'][0]['metricValues'][1]['value'] ?? 0);
            $output->writeln(sprintf(
                'GA4 %d jours : sessions/pages/sources/tendances récupérées (%d sessions, %d pages vues).',
                $days,
                $sessions,
                $pageViews
            ));
        }

        return Command::SUCCESS;
    }
}

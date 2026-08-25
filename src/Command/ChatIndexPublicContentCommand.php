<?php

namespace App\Command;

use App\Service\Chat\ChatPublicContentIndexer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:chat:index-public-content', description: 'Rebuild the public OLING chat knowledge index')]
class ChatIndexPublicContentCommand extends Command
{
    public function __construct(private readonly ChatPublicContentIndexer $indexer)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $result = $this->indexer->rebuild();

        $io->success(sprintf('%d documents indexés.', $result['indexed']));
        $io->writeln(sprintf('%d documents ignorés.', count($result['ignored'])));

        if ($result['ignored'] !== []) {
            $io->section('Documents ignorés');
            $io->listing($result['ignored']);
        }

        if ($result['references_without_safe_summary'] !== []) {
            $io->warning('Références sans résumé sûr détectées.');
            $io->listing($result['references_without_safe_summary']);
        }

        return Command::SUCCESS;
    }
}

<?php

namespace App\Command;

use App\Entity\ChatConversation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:chat:purge-expired', description: 'Purge les conversations de chat arrivées en fin de rétention.')]
class PurgeChatConversationsCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $qb = $this->entityManager->createQueryBuilder();
        $conversations = $qb
            ->select('c')
            ->from(ChatConversation::class, 'c')
            ->where('c.retentionPurgeAt <= :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getResult();

        foreach ($conversations as $conversation) {
            $this->entityManager->remove($conversation);
        }

        $this->entityManager->flush();

        $output->writeln(sprintf('%d conversation(s) supprimée(s).', count($conversations)));

        return Command::SUCCESS;
    }
}

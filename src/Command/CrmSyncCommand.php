<?php
namespace App\Command;

use App\Service\Crm\{CrmConfig,CrmStore,CrmSync,FormQueue};
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputInterface,InputOption};
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name:'app:crm:sync', description:'Synchronisation Microsoft 365 en production uniquement.')]
final class CrmSyncCommand extends Command
{
    public function __construct(private CrmConfig $config,private CrmStore $store,private CrmSync $sync,private FormQueue $forms) { parent::__construct(); }
    protected function configure(): void { $this->addOption('since',null,InputOption::VALUE_REQUIRED,'Reprise depuis AAAA-MM-JJ')->addOption('scheduled',null,InputOption::VALUE_NONE,'Respecter activation et cadence horaire'); }
    protected function execute(InputInterface $input,OutputInterface $output): int
    {
        if ($this->config->enabled('scheduler')) { try { $this->forms->flush(); } catch (\Throwable) { $output->writeln('Formulaires : reprise nécessaire.'); } }
        if ($input->getOption('scheduled')) {
            if (!$this->config->enabled('scheduler') || !$this->config->graphReady()) { $output->writeln('Automatisation inactive.'); return self::SUCCESS; }
            $state=$this->store->state('email');
            if (empty($state['next']) && !$this->store->state('sync_request') && strtotime($state['last_success']??'1970-01-01')>time()-3600) { return self::SUCCESS; }
        }
        try { $output->writeln(json_encode($this->sync->run($input->getOption('since')),JSON_THROW_ON_ERROR)); return self::SUCCESS; }
        catch (\Throwable) { $output->writeln('<error>Synchronisation non effectuée. Consulter l’état dans /admin/crm.</error>'); return self::FAILURE; }
    }
}

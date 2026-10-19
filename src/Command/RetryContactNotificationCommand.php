<?php
namespace App\Command;

use App\Repository\MessagerieRepository;
use App\Service\ContactNotificationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputInterface,InputArgument,InputOption};
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name:'app:contact:retry-notification',description:'Review/retry one failed or pending contact notification; no new contact or analytics event.')]
final class RetryContactNotificationCommand extends Command
{
    public function __construct(private readonly MessagerieRepository $messages,private readonly ContactNotificationService $notifications) { parent::__construct(); }
    protected function configure(): void {
        $this->addArgument('submission-id',InputArgument::REQUIRED,'Opaque receipt ID')->addOption('execute',null,InputOption::VALUE_NONE,'Send once after reviewing the current status');
    }
    protected function execute(InputInterface $input,OutputInterface $output): int {
        $id=$input->getArgument('submission-id');
        if(!is_string($id)||!preg_match('/^[a-f0-9]{32}$/D',$id)){ $output->writeln('Invalid receipt ID');return Command::INVALID; }
        $message=$this->messages->findOneBy(['submissionId'=>$id]);
        if(!$message){$output->writeln('Receipt not found');return Command::FAILURE;}
        $status=$message->getNotificationStatus();$output->writeln('Notification status: '.$status);
        if(!in_array($status,['pending','failed'],true)){$output->writeln('No retry: sent, uncertain sending or historical unknown requires separate review.');return Command::SUCCESS;}
        if(!$input->getOption('execute')){$output->writeln('Dry run; use --execute to retry this notification only.');return Command::SUCCESS;}
        $status=$this->notifications->notify($message);$output->writeln('Notification status: '.$status);
        return $status==='sent'?Command::SUCCESS:Command::FAILURE;
    }
}

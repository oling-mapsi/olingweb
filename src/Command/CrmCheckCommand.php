<?php
namespace App\Command;
use App\Service\Crm\{CrmStore,EmailExtractor};
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\{Request,RequestStack,Session\Session,Session\Storage\MockArraySessionStorage};
use Twig\Environment;
#[AsCommand(name:'app:crm:check',description:'Recette CRM transactionnelle, sans données fictives persistées.')]
final class CrmCheckCommand extends Command
{
 public function __construct(private Connection $db,private Environment $twig,private RequestStack $requests){parent::__construct();}
 protected function execute(InputInterface $input,OutputInterface $output): int
 {
 $db=$this->db;
$db->beginTransaction();
try {
 $store=new CrmStore($db);$extractor=new EmailExtractor();
 $m=['id'=>'recipe-'.bin2hex(random_bytes(12)),'subject'=>'Recette fictive — demande de devis','conversationId'=>'recipe-'.bin2hex(random_bytes(12)),'from'=>['emailAddress'=>['name'=>'Contact fictif','address'=>'client@example.invalid']],'body'=>['content'=>'Nous souhaitons un accompagnement. Merci de proposer un devis.'],'webLink'=>'https://example.org/recette'];
 $s=$extractor->extract($m,'demo@oling.fr');if(!$s['commercial']) {throw new \RuntimeException('Detection failed');}
 $r=$store->ingest($s);$id=$r['id'];if(!$store->ingest($s)['duplicate']) {throw new \RuntimeException('Dedup failed');}
 $d=$store->get($id);$store->edit($id,['next_action'=>'Appeler','organization'=>'Correction manuelle'],'sent','private','Note fictive de recette','recette',$d['version']);
 $s['external_id'].='-reply';$s['fields']['organization']='Valeur extraite';$s['suggested_status']='won';$store->ingest($s);$d=$store->get($id);
 if($d['status']!=='sent'||$d['organization']!=='Correction manuelle'||$d['next_action']!=='Appeler'||count($store->history($id))!==3) {throw new \RuntimeException('Preservation failed');}
 // Render production templates against synthetic data; authenticated HTTP is covered locally.
 $req=Request::create('/admin/crm/'.$id,'GET',[],[],[],['HTTP_HOST'=>'oling.fr','HTTPS'=>'on']);
 $req->attributes->set('_route','admin_crm_show');
 $req->setSession(new Session(new MockArraySessionStorage()));
 $this->requests->push($req);
 $html=$this->twig->render('admin/crm/show.html.twig',['deal'=>$d,'history'=>$store->history($id),'candidates'=>[],'statuses'=>CrmStore::STATUSES]);
 if(!str_contains($html,'Note fictive de recette')) {throw new \RuntimeException('Template rendering failed');}
 $db->rollBack();$output->writeln(json_encode(['identification'=>'PASS','creation'=>'PASS','deduplication'=>'PASS','manual_preservation'=>'PASS','template_rendering'=>'PASS','persisted_test_rows'=>0])); return self::SUCCESS;
} catch(\Throwable $e) {if($db->isTransactionActive()){$db->rollBack();}$output->writeln('<error>'.$e->getMessage().'</error>');return self::FAILURE;}

 }
}

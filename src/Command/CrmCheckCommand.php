<?php
namespace App\Command;
use App\Service\Crm\{CrmStore,EmailExtractor,FormIntake};
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
 $m=['id'=>'recipe-'.bin2hex(random_bytes(12)),'subject'=>'Recette fictive — demande de devis','conversationId'=>'recipe-'.bin2hex(random_bytes(12)),'from'=>['emailAddress'=>['name'=>'Contact fictif','address'=>'client@example.invalid']],'body'=>['content'=>'Nous souhaitons un accompagnement. Pouvez-vous nous proposer une mission ?'],'webLink'=>'https://example.org/recette'];
 $s=$extractor->extract($m,'demo@oling.fr');if(!$s['commercial']) {throw new \RuntimeException('Detection failed');}
 $r=$store->ingest($s);$id=$r['id'];if(!$store->ingest($s)['duplicate']) {throw new \RuntimeException('Dedup failed');}
 $d=$store->get($id);$store->edit($id,['next_action'=>'Appeler','organization'=>'Correction manuelle'],'sent','private','Note fictive de recette','recette',$d['version']);
 $sent=$store->get($id);$expected=(new \DateTimeImmutable('now',new \DateTimeZone('Europe/Paris')))->modify('+7 days')->format('Y-m-d');
 if($sent['next_action']!=='Relancer le client sur l’offre envoyée'||(new \DateTimeImmutable($sent['reminder']))->setTimezone(new \DateTimeZone('Europe/Paris'))->format('Y-m-d')!==$expected) {throw new \RuntimeException('Client J+7 follow-up failed');}
 $store->edit($id,['next_action'=>'Appeler'],'sent','private','','recette',$sent['version']);
 $s['external_id'].='-reply';$s['fields']['organization']='Valeur extraite';$s['suggested_status']='won';$store->ingest($s);$d=$store->get($id);
 if($d['status']!=='sent'||$d['organization']!=='Correction manuelle'||$d['next_action']!=='Appeler'||count($store->history($id))!==4) {throw new \RuntimeException('Preservation failed');}
 $store->setCcReceived($id,true,$d['version'],'recette');$s['external_id'].='-cc';$s['fields']['cc_received']=false;$store->ingest($s);$d=$store->get($id);
 if(!$d['cc_received']||$d['status']!=='sent'||$d['next_action']!=='Appeler') {throw new \RuntimeException('CC preservation failed');}
 $t=['origin'=>'watch','identity'=>'recipe-tender-'.$id,'external_id'=>'recipe-tender-'.$id,'title'=>'Avis fictif','kind'=>'public','fields'=>['reference'=>'TEST-'.$id],'follow'=>false];
 $tid=$store->ingest($t)['id'];$store->decide($tid,'reject',2,'recette');$t['external_id'].='-update';$t['follow']=true;$store->ingest($t);
 if($store->get($tid)['decision']!=='reject'){throw new \RuntimeException('Rejected tender resurrected');}
 $beforeDce=$store->get($tid);$store->setDceReceived($tid,true,$beforeDce['version'],'recette');
 $t['external_id'].='-dce';$t['fields']['dce_received']=false;$store->ingest($t);$afterDce=$store->get($tid);
 if(!$afterDce['dce_received']||$afterDce['status']!==$beforeDce['status']||$afterDce['decision']!==$beforeDce['decision']) {throw new \RuntimeException('DCE preservation failed');}
 $p=['site'=>'OLING','reference'=>$id,'fields'=>['first_name'=>'Camille','last_name'=>'Exemple','email'=>'test@example.invalid','summary'=>'Besoin intégral fictif']];
 $fi=new FormIntake($store);$fid=$fi->receive($p)['id'];if($fi->receive($p,'recipe-email-'.$id,'https://outlook.office.com/mail/id/test')['id']!==$fid){throw new \RuntimeException('Form replay failed');}
 // Render production templates against synthetic data; authenticated HTTP is covered locally.
 $req=Request::create('/admin/crm/'.$id,'GET',[],[],[],['HTTP_HOST'=>'oling.fr','HTTPS'=>'on']);
 $req->attributes->set('_route','admin_crm_show');
 $req->setSession(new Session(new MockArraySessionStorage()));
 $this->requests->push($req);
 $html=$this->twig->render('admin/crm/show.html.twig',['deal'=>$d,'history'=>$store->history($id),'candidates'=>[],'statuses'=>CrmStore::STATUSES]);
 if(!str_contains($html,'Note fictive de recette')) {throw new \RuntimeException('Template rendering failed');}
 $store->delete($tid,$store->get($tid)['version']);$t['external_id'].='-tomorrow';
 if(empty($store->ingest($t)['deleted'])) {throw new \RuntimeException('Deleted tender resurrected');}
 $db->rollBack();$output->writeln(json_encode(['identification'=>'PASS','creation'=>'PASS','deduplication'=>'PASS','manual_preservation'=>'PASS','client_offer_followup'=>'PASS','dce_confirmation'=>'PASS','cc_confirmation'=>'PASS','template_rendering'=>'PASS','tender_decision'=>'PASS','form_email_dedup'=>'PASS','delete_reimport_block'=>'PASS','persisted_test_rows'=>0])); return self::SUCCESS;
} catch(\Throwable $e) {if($db->isTransactionActive()){$db->rollBack();}$output->writeln('<error>'.$e->getMessage().'</error>');return self::FAILURE;}

 }
}

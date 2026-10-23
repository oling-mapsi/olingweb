<?php
// Production-compatible synthetic receipt test. No mail, model call or persisted test data.
if(PHP_SAPI!=='cli'){throw new RuntimeException('CLI only.');}
$root=dirname(__DIR__,2);require $root.'/vendor/autoload.php';(new Symfony\Component\Dotenv\Dotenv())->bootEnv($root.'/.env');
$kernel=new App\Kernel('prod',false);$kernel->boot();$em=$kernel->getContainer()->get('doctrine')->getManager();$db=$em->getConnection();
$store=new App\Service\Crm\CrmStore($db);$dir=sys_get_temp_dir().'/crm-chat-recipe-'.bin2hex(random_bytes(8));mkdir($dir,0700);
$config=new App\Service\Crm\CrmConfig('test');(new ReflectionProperty($config,'environment'))->setValue($config,'prod');(new ReflectionProperty($config,'settings'))->setValue($config,['forms_enabled'=>true,'forms_dir'=>$dir]);
$queue=new App\Service\Crm\FormQueue($config,new App\Service\Crm\FormIntake($store),$store,$db);
$subscriber=new App\Service\Crm\ContactReceiptSubscriber($queue,$db,$config,new Psr\Log\NullLogger());
$code=0;$before=(int)$db->fetchOne('SELECT COUNT(*) FROM crm_deal');$db->beginTransaction();
try {
 $now=new DateTimeImmutable();$token=bin2hex(random_bytes(24));
 $conversation=(new App\Entity\ChatConversation())->setPublicToken($token)->setStatus('submitted')->setSourcePath('/contact')->setSourceUrl('https://example.invalid/contact')->setSubmittedAt($now)->setConsentAt($now);
 $lead=(new App\Entity\ChatLead())->setConversation($conversation)->setFullName('Contact fictif recette')->setEmail('recipe@example.invalid')->setPhone('0100000000')->setCompany('Société fictive recette')->setNeedDescription('Besoin fictif complet de recette du formulaire intégré au chat.')->setRgpdConsent(true)->setRgpdConsentAt($now)->setCreatedAt($now);
 $em->persist($conversation);$em->persist($lead);$em->flush();
 $request=Symfony\Component\HttpFoundation\Request::create('/api/chat/conversations/'.$token.'/lead','POST');$request->attributes->set('_route','api_chat_conversation_lead');$request->attributes->set('token',$token);
 $dispatch=function()use($subscriber,$kernel,$request){$response=new Symfony\Component\HttpFoundation\JsonResponse(['success'=>true]);$subscriber->onResponse(new Symfony\Component\HttpKernel\Event\ResponseEvent($kernel,$request,Symfony\Component\HttpKernel\HttpKernelInterface::MAIN_REQUEST,$response));return json_decode($response->getContent(),true);};
 if(($dispatch()['crm_status']??'')!=='delivered'){throw new RuntimeException('Chat receipt delivery failed.');}
 $d=$store->identityDeal('form','OLING|chat-lead-'.$lead->getId());
 if(!$d||$d['status']!=='qualify'||$d['kind']!=='private'||$d['form_name']!=='chat_contact'||$d['summary']!==$lead->getNeedDescription()){throw new RuntimeException('CRM qualification mapping failed.');}
 $dispatch();if(count($store->history($d['id']))!==1){throw new RuntimeException('Chat duplicate created.');}
 $store->delete($d['id'],$d['version']);if(($dispatch()['crm_status']??'')!=='ignored'){throw new RuntimeException('Deleted chat lead recreated.');}
 $db->rollBack();if((int)$db->fetchOne('SELECT COUNT(*) FROM crm_deal')!==$before){throw new RuntimeException('Unexpected persisted record.');}
 echo json_encode(['chat_form_delivery'=>'PASS','client_qualification'=>'PASS','chat_deduplication'=>'PASS','deleted_chat_block'=>'PASS','persisted_test_rows'=>0]).PHP_EOL;
}catch(Throwable $e){if($db->isTransactionActive()){$db->rollBack();}fwrite(STDERR,$e->getMessage().PHP_EOL);$code=1;}
finally{foreach(glob($dir.'/*')?:[] as $file){unlink($file);}rmdir($dir);}

exit($code);

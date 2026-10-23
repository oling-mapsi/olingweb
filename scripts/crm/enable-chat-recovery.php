<?php
// Explicit cutover only: never pull historical chat leads without selecting a boundary.
if(PHP_SAPI!=='cli'||!function_exists('posix_geteuid')||posix_geteuid()!==0){throw new RuntimeException('Server root CLI required.');}
$first=$argv[1]??'';if(!ctype_digit($first)||(int)$first<1){throw new InvalidArgumentException('First lead id to recover required.');}
$root=dirname(__DIR__,2);require $root.'/vendor/autoload.php';(new Symfony\Component\Dotenv\Dotenv())->bootEnv($root.'/.env');
$kernel=new App\Kernel('prod',false);$kernel->boot();$db=$kernel->getContainer()->get('doctrine')->getConnection();$store=new App\Service\Crm\CrmStore($db);$config=new App\Service\Crm\CrmConfig('prod');
if(!$config->enabled('forms')||!$config->enabled('scheduler')||$config->graphReady()){throw new RuntimeException('Forms-only production configuration required.');}
if($store->state('chat_forms')){throw new RuntimeException('Recovery boundary already configured.');}
if(!$db->fetchOne('SELECT id FROM chat_lead WHERE id=? AND rgpd_consent=1',[(int)$first])){throw new RuntimeException('First consented receipt missing.');}
$store->setState('chat_forms',['cursor'=>(int)$first-1,'enabled_at'=>App\Service\Crm\CrmStore::now()]);
echo json_encode(['recovery_from_lead'=>(int)$first,'historical_leads_excluded'=>true]).PHP_EOL;

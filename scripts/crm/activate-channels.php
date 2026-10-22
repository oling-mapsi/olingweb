<?php
// Server-only post-release operation. Never exports business data.
require dirname(__DIR__,2).'/vendor/autoload.php';
(new Symfony\Component\Dotenv\Dotenv())->bootEnv(dirname(__DIR__,2).'/.env');
$kernel=new App\Kernel('prod',false);$kernel->boot();$db=$kernel->getContainer()->get('doctrine')->getConnection();
$store=new App\Service\Crm\CrmStore($db);
echo json_encode(['legacy_review'=>$store->reviewLegacy(),'deals'=>(int)$db->fetchOne('SELECT COUNT(*) FROM crm_deal')]),"\n";

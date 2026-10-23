<?php
// Server-only operation after a verified encrypted deployment backup.
if (PHP_SAPI!=='cli' || !function_exists('posix_geteuid') || posix_geteuid()!==0) { throw new RuntimeException('Server root CLI required.'); }
$root=dirname(__DIR__,2);require $root.'/vendor/autoload.php';
(new Symfony\Component\Dotenv\Dotenv())->bootEnv($root.'/.env');
$plan=json_decode(file_get_contents('/var/lib/oling-crm/client-reset-20261023.json'),true,512,JSON_THROW_ON_ERROR);
$backup=$argv[1]??'';
if (!preg_match('~^/var/backups/oling/oling-crm-[a-f0-9]{12}$~D',$backup) || !is_file($backup.'/offsite-verified') || !is_file($backup.'/snapshot.enc')) { throw new RuntimeException('Verified encrypted backup required.'); }
$lock=fopen('/var/lock/oling-deploy-cms-p0.lock','a');if(!flock($lock,LOCK_EX|LOCK_NB)) { throw new RuntimeException('Deployment in progress.'); }
$kernel=new App\Kernel('prod',false);$kernel->boot();$db=$kernel->getContainer()->get('doctrine')->getConnection();
$config=new App\Service\Crm\CrmConfig('prod');
if ($config->graphReady() || $config->get('client_intake')!=='forms_only') { throw new RuntimeException('Email intake must be disabled first.'); }
$store=new App\Service\Crm\CrmStore($db);
if ($store->state('client_reset')) { throw new RuntimeException('Already reset; do not delete newer requests.'); }
// Fingerprint all public/watch records and their related history before and after.
$fingerprint=function() use($db) {
    $rows=$db->fetchAllAssociative("SELECT * FROM crm_deal WHERE kind='public' OR JSON_UNQUOTE(JSON_EXTRACT(data,'$.origin'))='watch' ORDER BY id");
    $data=[$rows];foreach($rows as $r) { foreach(['crm_event','crm_source','crm_identity'] as $table) { $items=$db->fetchAllAssociative('SELECT * FROM '.$table.' WHERE deal_id=?',[$r['id']]);usort($items,fn($a,$b)=>strcmp(json_encode($a),json_encode($b)));$data[]=$items; } }
    return hash('sha256',json_encode($data));
};
$db->beginTransaction();
try {
    $before=$fingerprint();$result=(new App\Service\Crm\CrmClientReset($db,$store))->purge($plan['ids'],'Demande utilisateur du 23/10/2026');
    if ($result['deleted']!==$plan['count'] || $fingerprint()!==$before) { throw new RuntimeException('Scope verification failed.'); }
    $db->commit();echo json_encode($result+['public_records_unchanged'=>true]).PHP_EOL;
} catch (Throwable $e) { $db->rollBack();throw $e; }

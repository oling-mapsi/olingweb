<?php
// Local synthetic recipe only; no network, OAuth credential, shell or Codex invocation.
require __DIR__.'/bootstrap-local.php';
use App\Service\Ai\{AiArtifacts,AiStore,AiNativeRegistry,AiTrackingSchema};
$root=dirname(__DIR__,2);$database=$root.'/var/ai-fixture.sqlite';
if(!is_file($database))throw new RuntimeException('Run local-fixture.php first; no data import.');
$_ENV['DATABASE_URL']=$_SERVER['DATABASE_URL']='sqlite:///'.$database;
$kernel=new App\Kernel('test',false);$kernel->boot();$em=$kernel->getContainer()->get('doctrine')->getManager();$db=$em->getConnection();
$user=$em->getRepository(App\Entity\User::class)->findOneBy(['username'=>'ai-demo','email'=>'ai-demo@example.invalid']);
if(!$user)throw new RuntimeException('Synthetic marker missing.');
if(!$db->createSchemaManager()->tablesExist(['ai_tracking_receipt'])) {
    $s=new Doctrine\DBAL\Schema\Schema();AiTrackingSchema::add($s);foreach($s->toSql($db->getDatabasePlatform()) as $sql)$db->executeStatement($sql);
}
$a=new AiArtifacts($root);$registry=new AiNativeRegistry($db,new AiStore($db,$a),$a);$owner=$user->getId();$actor='fixture-native-pilot';$steps=[];
$call=function($tool,$args)use($registry,$owner,$actor,&$steps){$r=$registry->call($tool,$args,$owner,$actor);$steps[]=['operation'=>$tool,'response'=>$r];return $r;};
$m=$call('ai_mission_create',['event_id'=>'v1-fixture-create-20261024','title'=>'Recette V1 simple — tâche native fictive','brief'=>'Fixture de suivi uniquement : aucun vrai Codex, aucune donnée métier, aucun raccordement vivant.','project'=>'demo']);
for($cycle=0;$cycle<2;$cycle++) {
    $base=['mission_id'=>$m['mission_id'],'version'=>$m['version']];
    $m=$call('ai_task_associate',$base+['event_id'=>'v1-fixture-associate-'.$cycle,'task_id'=>'fixture-native-v1','task_url'=>'codex://threads/fixture-native-v1','model'=>'fixture-model-not-executed','effort'=>'medium']);
    $m=$call('ai_task_event',['mission_id'=>$m['mission_id'],'version'=>$m['version'],'run_id'=>$m['run_id'],'event_id'=>'v1-fixture-start-'.$cycle,'event'=>'started']);
    $args=['mission_id'=>$m['mission_id'],'version'=>$m['version'],'run_id'=>$m['run_id'],'event_id'=>'v1-fixture-result-'.$cycle,'summary'=>'Résultat synthétique du cycle '.$cycle.'. Aucun modèle exécuté.','proofs'=>[['label'=>'Lien factice de recette — ne prouve pas un test réel','url'=>'https://github.com/example/fixture/commit/abc']],'checks'=>[['label'=>'Contrôle déclaré fictif','outcome'=>'passed'],['label'=>'Inférence réelle','outcome'=>'not_run']]];
    $m=$call('ai_task_result',$args);if($registry->call('ai_task_result',$args,$owner,$actor)!==$m)throw new RuntimeException('Replay mismatch');
    $m=$call('ai_mission_review',['mission_id'=>$m['mission_id'],'version'=>$m['version'],'run_id'=>$m['run_id'],'event_id'=>'v1-fixture-review-'.$cycle,'verdict'=>$cycle?'approve':'correct','feedback'=>$cycle?'Revue fictive favorable. Validation humaine requise.':'Corriger le livrable fictif.','review_task_id'=>'fixture-review-v1-'.$cycle,'model'=>'fixture-review-model-not-executed','effort'=>'high']);
}
$report=['fixture_only'=>true,'live_native_connection'=>false,'model_executed'=>false,'steps'=>$steps,'current_status'=>$registry->detail($m['mission_id'],$owner)['mission']['status'],'mission_path'=>'/admin/ai/missions/'.$m['mission_id']];
file_put_contents($root.'/docs/ai/v1-native-fixture-result.json',json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)."\n");echo json_encode(['fixture_only'=>true,'mission_path'=>$report['mission_path'],'status'=>$report['current_status']])."\n";

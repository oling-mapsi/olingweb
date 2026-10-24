<?php
require __DIR__.'/bootstrap-local.php';
$database=dirname(__DIR__,2).'/var/ai-fixture.sqlite';
if(is_file($database)){fwrite(STDERR,"Fixture already exists; no reset performed.\n");exit(1);}
$_ENV['DATABASE_URL']=$_SERVER['DATABASE_URL']='sqlite:///'.$database;
$kernel=new App\Kernel('test',false);$kernel->boot();$em=$kernel->getContainer()->get('doctrine')->getManager();
(new Doctrine\ORM\Tools\SchemaTool($em))->createSchema($em->getMetadataFactory()->getAllMetadata());
$s=new Doctrine\DBAL\Schema\Schema();App\Service\Ai\AiSchema::add($s);\App\Service\Ai\AiTrackingSchema::add($s);
foreach($s->toSql($em->getConnection()->getDatabasePlatform()) as $sql)$em->getConnection()->executeStatement($sql);
$u=(new App\Entity\User())->setUsername('ai-demo')->setEmail('ai-demo@example.invalid')->setRoles(['ROLE_ADMIN']);
// Public synthetic fixture, strictly limited to the loopback-only router; no real account is provisioned.
$u->setPassword(password_hash('Demo-local-only-42!', PASSWORD_BCRYPT));$em->persist($u);$em->flush();
$reader=(new App\Entity\User())->setUsername('ai-demo-reader')->setEmail('ai-demo-reader@example.invalid')->setRoles(['ROLE_USER']);
$reader->setPassword(password_hash('Demo-local-only-42!', PASSWORD_BCRYPT));$em->persist($reader);$em->flush();
$store=new App\Service\Ai\AiStore($em->getConnection(),new App\Service\Ai\AiArtifacts(dirname(__DIR__,2)));
(new App\Service\Ai\AiNativeRegistry($em->getConnection(),$store,new App\Service\Ai\AiArtifacts(dirname(__DIR__,2))))->call('ai_mission_create',['title'=>'Préparer une page de présentation','brief'=>'Maquette locale uniquement. Vérifier la lisibilité mobile et fournir les preuves.','project'=>'demo','event_id'=>'fixture-mission-create'],$u->getId(),'human-'.$u->getId());
chmod($database,0600);echo "Synthetic local fixture ready.\n";

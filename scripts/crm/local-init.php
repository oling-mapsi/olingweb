<?php
// Synthetic DB only: explicit local path, never the application's configured production connection.
require dirname(__DIR__,2).'/vendor/autoload.php';
use App\Service\Crm\{CrmSchema,CrmStore};
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
$root=dirname(__DIR__,2); @mkdir($root.'/var',0700,true);
$db=DriverManager::getConnection(['driver'=>'pdo_sqlite','path'=>$root.'/var/crm-local.db']);
if ($db->createSchemaManager()->tablesExist(['app_user'])) { echo "Base locale déjà initialisée.\n"; exit; }
(new Symfony\Component\Dotenv\Dotenv())->bootEnv($root.'/.env');
$_ENV['DATABASE_URL']=$_SERVER['DATABASE_URL']='sqlite:///'.$root.'/var/crm-local.db';
$_ENV['APP_ENV']=$_SERVER['APP_ENV']='dev';
$kernel=new App\Kernel('dev',true); $kernel->boot();
$em=$kernel->getContainer()->get('doctrine')->getManager();
(new Doctrine\ORM\Tools\SchemaTool($em))->createSchema($em->getMetadataFactory()->getAllMetadata());
$s=new Schema(); CrmSchema::add($s); foreach($s->toSql($db->getDatabasePlatform()) as $sql) { $db->executeStatement($sql); }
$password=bin2hex(random_bytes(10));
$user=(new App\Entity\User())->setUsername('crm-demo')->setEmail('crm-demo@example.invalid')->setRoles(['ROLE_ADMIN'])->setPassword(password_hash($password,PASSWORD_BCRYPT)); $em->persist($user); $em->flush();
file_put_contents($root.'/var/crm-local-login.txt',"Utilisateur : crm-demo\nMot de passe : ".$password."\n"); chmod($root.'/var/crm-local-login.txt',0600);
$store=new CrmStore($db);
foreach ([['Atelier Démo','Mission de conseil fictive','private'],['Commune Exemple','AMOA SI — consultation fictive','public']] as [$organization,$title,$kind]) {
 $id=$store->create(['organization'=>$organization,'title'=>$title,'email'=>'contact@example.invalid','summary'=>'Donnée fictive de développement.'],$kind);
 $store->event($id,'manual',['actor'=>'Démonstration','note'=>'Exemple fictif, aucun email envoyé.']);
}
echo "Base synthétique créée. Identifiants : var/crm-local-login.txt\n";

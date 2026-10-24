<?php
namespace App\Tests\Ai;
use App\Service\Ai\{AiSchema,AiStore,AiArtifacts};
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use PHPUnit\Framework\TestCase;
final class AiConcurrencyTest extends TestCase
{
    public function testConcurrentIndependentConnectionsCannotReserveTwoMissionsForOneExecutor():void
    {
        if(!function_exists('pcntl_fork'))self::markTestSkipped('pcntl unavailable; multiprocess claim not tested');
        $dir=sys_get_temp_dir().'/ai-concurrency-'.bin2hex(random_bytes(5));mkdir($dir,0700);$file=$dir.'/fixture.sqlite';$children=[];
        try {
            $db=DriverManager::getConnection(['driver'=>'pdo_sqlite','path'=>$file]);$db->executeStatement('CREATE TABLE app_user (id INTEGER PRIMARY KEY)');$db->insert('app_user',['id'=>1]);
            $schema=new Schema();AiSchema::add($schema);foreach($schema->toSql($db->getDatabasePlatform()) as $sql)$db->executeStatement($sql);
            $store=new AiStore($db,new AiArtifacts($dir));
            foreach([1,2] as $n){$id=$store->create(1,'Concurrent fixture '.$n,'Synthetic only','demo','create-concurrent-'.$n);$store->launch($id,1,1,'launch-concurrent-'.$n);}
            $db->close();
            foreach([1,2] as $n) {
                $pid=pcntl_fork();if($pid===-1)throw new \RuntimeException('fork failed');
                if($pid===0) {
                    try {
                        $connection=DriverManager::getConnection(['driver'=>'pdo_sqlite','path'=>$file]);$connection->executeStatement('PRAGMA busy_timeout=3000');$s=new AiStore($connection,new AiArtifacts($dir));
                        file_put_contents($dir.'/ready-'.$n,'ready');$deadline=microtime(true)+3;
                        while(!file_exists($dir.'/go')&&microtime(true)<$deadline)usleep(1000);
                        $claim=$s->claim(1,'mac-local',['demo'],time());file_put_contents($dir.'/result-'.$n,json_encode(['id'=>$claim['id']??null]));$connection->close();exit(0);
                    }catch(\Throwable $e){file_put_contents($dir.'/result-'.$n,json_encode(['error'=>get_class($e)]));exit(1);}
                }$children[]=$pid;
            }
            $deadline=microtime(true)+3;while((!file_exists($dir.'/ready-1')||!file_exists($dir.'/ready-2'))&&microtime(true)<$deadline)usleep(1000);
            file_put_contents($dir.'/go','go');foreach($children as $pid){pcntl_waitpid($pid,$status);self::assertSame(0,pcntl_wexitstatus($status));}$children=[];
            $results=[json_decode(file_get_contents($dir.'/result-1'),true),json_decode(file_get_contents($dir.'/result-2'),true)];
            self::assertCount(1,array_filter($results,fn($r)=>isset($r['id'])));
            $check=DriverManager::getConnection(['driver'=>'pdo_sqlite','path'=>$file]);self::assertSame(1,(int)$check->fetchOne("SELECT COUNT(*) FROM ai_run WHERE status='running'"));$check->close();
        } finally {foreach($children as $pid)pcntl_waitpid($pid,$status);foreach(glob($dir.'/*')?:[] as $p)unlink($p);rmdir($dir);}
    }
}

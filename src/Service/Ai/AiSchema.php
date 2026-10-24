<?php
namespace App\Service\Ai;
use Doctrine\DBAL\Schema\Schema;

/** DBAL-managed aggregates, like CRM. Apply only the dedicated additive migration. */
final class AiSchema
{
    public static function add(Schema $schema): void
    {
        foreach (['mission','prompt','prompt_version','run','asset','review','event','relay'] as $name) {
            $t=$schema->createTable('ai_'.$name);
            $t->addColumn('id','string',['length'=>32]); $t->setPrimaryKey(['id']);
        }
        $t=$schema->getTable('ai_prompt');
        $t->addColumn('title','string',['length'=>180]); $t->addColumn('owner_id','integer');
        $t=$schema->getTable('ai_prompt_version');
        foreach (['prompt_id'=>32,'content_hash'=>64] as $name=>$length) $t->addColumn($name,'string',['length'=>$length]);
        $t->addColumn('content','text'); $t->addColumn('created_at','integer');
        $t->addForeignKeyConstraint('ai_prompt',['prompt_id'],['id']);
        $t=$schema->getTable('ai_mission');
        foreach (['title'=>180,'project'=>80,'executor'=>80,'status'=>32,'prompt_version_id'=>32] as $name=>$length) $t->addColumn($name,'string',['length'=>$length]);
        $t->addColumn('brief','text');$t->addColumn('closure','text',['notnull'=>false]);
        foreach (['owner_id','version','corrections','created_at','updated_at'] as $name) $t->addColumn($name,'integer');
        $t->addIndex(['owner_id','status']);
        $t->addForeignKeyConstraint('app_user',['owner_id'],['id']);
        $t->addForeignKeyConstraint('ai_prompt_version',['prompt_version_id'],['id']);
        $t=$schema->getTable('ai_run');
        foreach (['mission_id'=>32,'role'=>20,'model'=>100,'effort'=>16,'status'=>32,'executor'=>80] as $name=>$length) $t->addColumn($name,'string',['length'=>$length]);
        foreach (['thread_id','turn_id'] as $name) $t->addColumn($name,'string',['length'=>100,'notnull'=>false]);
        foreach (['created_at','lease_until','heartbeat_at'] as $name) $t->addColumn($name,'integer',['default'=>0]);
        $t->addColumn('output','text',['notnull'=>false]); $t->addColumn('failure','text',['notnull'=>false]);
        $t->addColumn('request_key','string',['length'=>64]);$t->addUniqueIndex(['request_key']);
        $t->addColumn('ordinal','integer');$t->addUniqueIndex(['mission_id','ordinal']);$t->addIndex(['executor','status']);$t->addForeignKeyConstraint('ai_mission',['mission_id'],['id']);
        $t=$schema->getTable('ai_asset');
        foreach (['mission_id'=>32,'run_id'=>32,'title'=>180,'storage_key'=>80,'sha256'=>64,'mime'=>80] as $name=>$length) $t->addColumn($name,'string',['length'=>$length]);
        $t->addColumn('bytes','integer');$t->addColumn('created_at','integer');
        $t->addForeignKeyConstraint('ai_mission',['mission_id'],['id']);
        $t->addForeignKeyConstraint('ai_run',['run_id'],['id']);$t->addUniqueIndex(['run_id','sha256']);
        $t=$schema->getTable('ai_review');
        foreach (['mission_id'=>32,'run_id'=>32,'verdict'=>16] as $name=>$length) $t->addColumn($name,'string',['length'=>$length]);
        $t->addColumn('feedback','text');$t->addColumn('reviewer_id','integer');$t->addColumn('created_at','integer');
        $t->addForeignKeyConstraint('ai_mission',['mission_id'],['id']);
        $t->addForeignKeyConstraint('ai_run',['run_id'],['id']);
        $t=$schema->getTable('ai_event');
        $t->addColumn('mission_id','string',['length'=>32]);$t->addColumn('kind','string',['length'=>40]);
        $t->addColumn('data','text');$t->addColumn('created_at','integer');
        $t->addColumn('operation_key','string',['length'=>64,'notnull'=>false]);$t->addUniqueIndex(['operation_key']);
        $t->addColumn('ordinal','integer');$t->addUniqueIndex(['mission_id','ordinal']);$t->addIndex(['mission_id','created_at']);$t->addForeignKeyConstraint('ai_mission',['mission_id'],['id']);
        $t=$schema->getTable('ai_relay');
        $t->addColumn('executor','string',['length'=>80]);$t->addColumn('owner_id','integer');$t->addColumn('seen_at','integer');$t->addUniqueIndex(['executor','owner_id']);
    }
}

<?php
namespace App\Service\Ai;
use Doctrine\DBAL\Schema\Schema;
final class AiTrackingSchema
{
    public static function add(Schema $schema): void
    {
        $t=$schema->createTable('ai_native_binding');
        $t->addColumn('mission_id','string',['length'=>32]);$t->setPrimaryKey(['mission_id']);
        $t->addColumn('task_id','string',['length'=>100]);$t->addUniqueIndex(['task_id']);
        $t->addColumn('task_url','string',['length'=>500]);$t->addColumn('executor','string',['length'=>80]);
        $t->addForeignKeyConstraint('ai_mission',['mission_id'],['id']);
        $t=$schema->createTable('ai_tracking_receipt');
        $t->addColumn('operation_key','string',['length'=>64]);$t->setPrimaryKey(['operation_key']);
        $t->addColumn('owner_id','integer');$t->addColumn('payload_hash','string',['length'=>64]);
        $t->addColumn('response','text');$t->addColumn('created_at','integer');
        $t->addForeignKeyConstraint('app_user',['owner_id'],['id']);
    }
}

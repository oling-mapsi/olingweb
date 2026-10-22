<?php
namespace App\Service\Crm;

use Doctrine\DBAL\Schema\Schema;

final class CrmSchema
{
    public static function add(Schema $schema): void
    {
        $t = $schema->createTable('crm_deal');
        $t->addColumn('id', 'string', ['length'=>32]);
        $t->addColumn('data', 'text');
        $t->addColumn('status', 'string', ['length'=>24]);
        $t->addColumn('kind', 'string', ['length'=>16]);
        $t->addColumn('contact_email', 'string', ['length'=>254, 'default'=>'']);
        $t->addColumn('updated_at', 'string', ['length'=>25]);
        $t->addColumn('version', 'integer', ['default'=>1]);
        $t->setPrimaryKey(['id']); $t->addIndex(['status', 'updated_at']); $t->addIndex(['contact_email']);
        foreach (['crm_source'=>'source_key', 'crm_identity'=>'identity_key'] as $name=>$key) {
            $t=$schema->createTable($name);
            $t->addColumn($key,'string',['length'=>64]); $t->setPrimaryKey([$key]);
            $t->addColumn('deal_id','string',['length'=>32]); $t->addIndex(['deal_id']);
            $t->addForeignKeyConstraint('crm_deal',['deal_id'],['id']);
        }
        $t=$schema->createTable('crm_event');
        $t->addColumn('id','string',['length'=>32]); $t->setPrimaryKey(['id']);
        $t->addColumn('deal_id','string',['length'=>32]);
        $t->addColumn('created_at','string',['length'=>25]); $t->addIndex(['deal_id','created_at']);
        $t->addColumn('kind','string',['length'=>24]);
        $t->addColumn('data','text');
        $t->addForeignKeyConstraint('crm_deal',['deal_id'],['id']);
        $t=$schema->createTable('crm_state');
        $t->addColumn('name','string',['length'=>80]); $t->setPrimaryKey(['name']);
        $t->addColumn('data','text');
    }
}

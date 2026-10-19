<?php

declare(strict_types=1);
namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/** Additive taxonomy only. No editorial row, slug or legacy FK is rewritten. */
final class Version20261014230000 extends AbstractMigration
{
    public function getDescription(): string { return 'Classification CMS indépendante des URL historiques, relations additives sans backfill.'; }
    public function up(Schema $schema): void
    {
        foreach (['services','site_page','projet'] as $name) {
            $table = $schema->getTable($name);
            $table->addColumn('catalog_practice_id','integer',['notnull'=>false]);
            $table->addIndex(['catalog_practice_id'],'idx_cms_'.$name.'_practice');
            $table->addForeignKeyConstraint('practice',['catalog_practice_id'],['id'],['onDelete'=>'SET NULL'],'fk_cms_'.$name.'_practice');
        }
        $schema->getTable('site_page')->addColumn('catalog_type','string',['length'=>32,'notnull'=>false]);
        foreach ($this->joins() as $name=>[$left,$right,$leftTable,$rightTable]) {
            $table = $schema->createTable($name);
            $table->addColumn($left,'integer'); $table->addColumn($right,'integer');
            $table->setPrimaryKey([$left,$right]); $table->addIndex([$right]);
            $table->addForeignKeyConstraint($leftTable,[$left],['id'],['onDelete'=>'CASCADE']);
            $table->addForeignKeyConstraint($rightTable,[$right],['id'],['onDelete'=>'CASCADE']);
        }
    }
    public function down(Schema $schema): void
    {
        // Refuse to discard entered classification during rollback.
        foreach (array_keys($this->joins()) as $table) { $this->abortIf((int)$this->connection->fetchOne('SELECT COUNT(*) FROM '.$table)>0,'Exportez les relations CMS avant rollback.'); }
        foreach (['services','site_page','projet'] as $name) { $this->abortIf((int)$this->connection->fetchOne('SELECT COUNT(*) FROM '.$name.' WHERE catalog_practice_id IS NOT NULL')>0,'Exportez les classifications CMS avant rollback.'); }
        $this->abortIf((int)$this->connection->fetchOne('SELECT COUNT(*) FROM site_page WHERE catalog_type IS NOT NULL')>0,'Exportez les types CMS avant rollback.');
        foreach (array_keys($this->joins()) as $name) { $schema->dropTable($name); }
        foreach (['services','site_page','projet'] as $name) {
            $table=$schema->getTable($name);
            $table->removeForeignKey('fk_cms_'.$name.'_practice'); $table->dropIndex('idx_cms_'.$name.'_practice'); $table->dropColumn('catalog_practice_id');
        }
        $schema->getTable('site_page')->dropColumn('catalog_type');
    }
    private function joins(): array
    {
        return [
            'cms_service_secondary_practice'=>['services_id','practice_id','services','practice'],
            'cms_page_service'=>['site_page_id','services_id','site_page','services'],
            'cms_page_expertise'=>['site_page_source','site_page_target','site_page','site_page'],
            'cms_project_expertise'=>['projet_id','site_page_id','projet','site_page'],
            'cms_project_sector'=>['projet_id','metier_id','projet','metier'],
        ];
    }
}

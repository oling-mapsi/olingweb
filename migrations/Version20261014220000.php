<?php
namespace DoctrineMigrations;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
final class Version20261014220000 extends AbstractMigration {
 public function getDescription(): string {return 'Médias CMS facultatifs, propriétaires distincts et fichiers conservés.';}
 public function up(Schema $schema): void {
  $t=$schema->createTable('cms_media');$t->addColumn('id','integer',['autoincrement'=>true]);$t->setPrimaryKey(['id']);
  foreach(['slot'=>16,'locale'=>8,'path'=>255,'alt'=>255,'placement'=>32,'display_mode'=>16,'size'=>16] as $c=>$l)$t->addColumn($c,'string',['length'=>$l]);
  $t->addColumn('caption','text',['notnull'=>false]);foreach(['sort_order','focal_x','focal_y'] as $c)$t->addColumn($c,'integer');
  $t->addIndex(['locale','sort_order','id'],'idx_cms_media_locale_order');
  foreach(['service'=>'services','page'=>'site_page','practice'=>'practice','project'=>'projet','sector'=>'metier','legal'=>'legal_page'] as $owner=>$table){$c=$owner.'_id';$t->addColumn($c,'integer',['notnull'=>false]);$t->addIndex([$c]);$t->addForeignKeyConstraint($table,[$c],['id'],['onDelete'=>'CASCADE']);}
 }
 public function down(Schema $schema): void {$this->abortIf((int)$this->connection->fetchOne('SELECT COUNT(*) FROM cms_media')>0,'Exportez les associations médias avant rollback.');$schema->dropTable('cms_media');}
}

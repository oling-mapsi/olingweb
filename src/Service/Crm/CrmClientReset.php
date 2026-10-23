<?php
namespace App\Service\Crm;

use Doctrine\DBAL\Connection;

/** Explicit maintenance operation: frozen IDs only, never a broad live DELETE. */
final class CrmClientReset
{
    public function __construct(private Connection $db, private CrmStore $store) {}
    public function purge(array $ids, string $actor): array
    {
        return $this->db->transactional(function() use($ids,$actor) {
            $deleted=0;
            foreach (array_unique($ids) as $id) {
                if (!is_string($id) || !preg_match('/^[a-f0-9]{32}$/D',$id)) { throw new \InvalidArgumentException('Identifiant invalide.'); }
                $d=$this->store->get($id);
                if ($d['kind']!=='private' || $d['origin']==='watch' || $d['review_required'] || !empty($d['merged_into'])) { throw new \RuntimeException('Le périmètre a changé ; suppression annulée.'); }
                foreach (['crm_identity'=>['identity_key','deleted_i:'],'crm_source'=>['source_key','deleted_s:']] as $table=>[$key,$prefix]) {
                    foreach ($this->db->fetchFirstColumn('SELECT '.$key.' FROM '.$table.' WHERE deal_id=?',[$id]) as $hash) { $this->store->setState($prefix.$hash,['at'=>CrmStore::now()]); }
                }
                // Keep only deduplication hashes, never customer fields or email excerpts.
                foreach (['crm_event','crm_source','crm_identity'] as $table) { $this->db->delete($table,['deal_id'=>$id]); }
                $deleted+=$this->db->delete('crm_deal',['id'=>$id]);
            }
            $result=['at'=>CrmStore::now(),'deleted'=>$deleted,'actor'=>$actor];
            $this->store->setState('client_reset',$result);return $result;
        });
    }
}

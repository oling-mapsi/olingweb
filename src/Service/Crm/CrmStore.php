<?php
namespace App\Service\Crm;

use Doctrine\DBAL\Connection;

final class CrmStore
{
    public const STATUSES = ['qualify'=>'À qualifier','follow'=>'À suivre','preparing'=>'En préparation','sent'=>'Offre envoyée / réponse déposée','discussion'=>'En discussion','won'=>'Gagné','lost'=>'Perdu','archived'=>'Archivé'];
    public const FIELDS = ['organization','title','summary','contact','role','email','phone','buyer','reference','lot','procedure','deadline','amount','amount_kind','owner','next_action','reminder','notice_url','sharepoint_url'];
    public function __construct(private Connection $db) {}
    public static function now(): string { return gmdate('Y-m-d\TH:i:s\Z'); }
    public static function json(array $a): string { return json_encode($a, JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR); }
    public function state(string $name): array { return json_decode($this->db->fetchOne('SELECT data FROM crm_state WHERE name=?',[$name]) ?: '{}',true); }
    public function setState(string $name, array $value): void
    {
        if ($this->db->fetchOne('SELECT name FROM crm_state WHERE name=?',[$name])) { $this->db->update('crm_state',['data'=>self::json($value)],['name'=>$name]); }
        else { $this->db->insert('crm_state',['name'=>$name,'data'=>self::json($value)]); }
    }
    public function get(string $id): array
    {
        $r=$this->db->fetchAssociative('SELECT * FROM crm_deal WHERE id=?',[$id]);
        if (!$r) { throw new \InvalidArgumentException('Affaire introuvable.'); }
        return array_merge(json_decode($r['data'],true), ['id'=>$r['id'],'status'=>$r['status'],'kind'=>$r['kind'],'version'=>(int)$r['version'],'updated_at'=>$r['updated_at']]);
    }
    public function all(): array { return array_map(fn($r)=>$this->get($r['id']),$this->db->fetchAllAssociative('SELECT id FROM crm_deal ORDER BY updated_at DESC')); }
    public function history(string $id): array
    {
        return array_map(fn($r)=>array_merge($r,['data'=>json_decode($r['data'],true)]),$this->db->fetchAllAssociative('SELECT * FROM crm_event WHERE deal_id=? ORDER BY created_at DESC,id DESC',[$id]));
    }
    public function event(string $id, string $kind, array $data): void
    {
        $this->db->insert('crm_event',['id'=>bin2hex(random_bytes(16)),'deal_id'=>$id,'created_at'=>self::now(),'kind'=>$kind,'data'=>self::json($data)]);
    }
    public function validate(array $input): array
    {
        $out=[];
        foreach (self::FIELDS as $key) {
            if (!array_key_exists($key,$input)) { continue; }
            if (!is_scalar($input[$key]) && $input[$key] !== null) { throw new \InvalidArgumentException('Champ invalide : '.$key); }
            $value=trim((string)$input[$key]);
            if (mb_strlen($value)>($key==='summary'?12000:1000)) { throw new \InvalidArgumentException('Champ trop long : '.$key); }
            if ($value!=='' && in_array($key,['notice_url','sharepoint_url']) && (!filter_var($value,FILTER_VALIDATE_URL) || parse_url($value,PHP_URL_SCHEME)!=='https')) { throw new \InvalidArgumentException('Lien HTTPS requis.'); }
            if ($value!=='' && $key==='email' && (!filter_var($value,FILTER_VALIDATE_EMAIL)||strlen($value)>254)) { throw new \InvalidArgumentException('Email invalide.'); }
            if ($value!=='' && $key==='amount' && !preg_match('/^\d{1,12}(\.\d{1,2})?$/D',$value)) { throw new \InvalidArgumentException('Montant positif, avec point décimal.'); }
            if ($value!=='' && in_array($key,['deadline','reminder'])) {
                if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(?::\d{2})?(Z|[+-]\d{2}:\d{2})?$/D',$value)) { throw new \InvalidArgumentException('Date ISO requise.'); }
                try { $date=new \DateTimeImmutable($value, new \DateTimeZone('Europe/Paris')); } catch (\Exception) { throw new \InvalidArgumentException('Date invalide.'); }
                if (\DateTimeImmutable::getLastErrors() !== false) { throw new \InvalidArgumentException('Date invalide.'); }
                $value=$date->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z');
            }
            $out[$key]=$value;
        }
        if (isset($out['amount_kind']) && !in_array($out['amount_kind'],['','estimated','proposed'])) { throw new \InvalidArgumentException('Nature du montant invalide.'); }
        return $out;
    }
    public function create(array $fields, string $kind='private', string $origin='manual'): string
    {
        $fields=$this->validate($fields);
        if (empty($fields['title']) || !in_array($kind,['private','public'])) { throw new \InvalidArgumentException('Intitulé et type requis.'); }
        $id=bin2hex(random_bytes(16));
        $fields += array_fill_keys(self::FIELDS,''); $fields['origin']=$origin; $fields['manual_fields']=[];
        $this->db->insert('crm_deal',['id'=>$id,'data'=>self::json($fields),'status'=>'qualify','kind'=>$kind,'contact_email'=>strtolower($fields['email']),'updated_at'=>self::now(),'version'=>1]);
        return $id;
    }
    private function save(array $d, int $version): void
    {
        $id=$d['id']; $status=$d['status']; $kind=$d['kind']; unset($d['id'],$d['status'],$d['kind'],$d['version'],$d['updated_at']);
        $n=$this->db->executeStatement('UPDATE crm_deal SET data=?,status=?,kind=?,contact_email=?,updated_at=?,version=version+1 WHERE id=? AND version=?',[self::json($d),$status,$kind,strtolower($d['email']),self::now(),$id,$version]);
        if ($n!==1) { throw new \RuntimeException('Cette affaire a changé. Rechargez la fiche avant de réessayer.'); }
    }
    public function edit(string $id, array $fields, string $status, string $kind, string $note, string $actor, int $version): void
    {
        if (!isset(self::STATUSES[$status]) || !in_array($kind,['private','public']) || mb_strlen($note)>12000) { throw new \InvalidArgumentException('Valeurs invalides.'); }
        $fields=$this->validate($fields);
        $this->db->transactional(function() use($id,$fields,$status,$kind,$note,$actor,$version) {
            $d=$this->get($id); $changes=[];
            foreach ($fields as $key=>$value) { if ($value!==$d[$key]) { $changes[$key]=['before'=>$d[$key],'after'=>$value]; $d[$key]=$value; $d['manual_fields'][$key]=true; } }
            if (empty($d['title'])) { throw new \InvalidArgumentException('Intitulé requis.'); }
            if ($status!==$d['status']) { $d['manual_fields']['status']=true; $changes['status']=['before'=>$d['status'],'after'=>$status]; }
            $d['status']=$status; $d['kind']=$kind; $this->save($d,$version);
            $this->event($id,'manual',['actor'=>$actor,'changes'=>$changes,'note'=>trim($note)]);
        });
    }
    // Exact thread/reference identities only. An email address alone never merges projects.
    public function ingest(array $source): array
    {
        foreach (['origin','external_id','identity','title'] as $k) { if (empty($source[$k]) || !is_string($source[$k])) { throw new \InvalidArgumentException('Source incomplète : '.$k); } }
        if (!in_array($source['origin'],['email','watch','form'])) { throw new \InvalidArgumentException('Origine invalide.'); }
        $source['fields']=$this->validate(($source['fields']??[]) + ['title'=>$source['title']]);
        foreach (['url'] as $key) { if (!empty($source[$key]) && (!filter_var($source[$key],FILTER_VALIDATE_URL) || parse_url($source[$key],PHP_URL_SCHEME)!=='https')) { throw new \InvalidArgumentException('Source HTTPS requise.'); } }
        return $this->db->transactional(function() use($source) {
            $key=hash('sha256',$source['origin'].'|'.$source['external_id']);
            $existing=$this->db->fetchOne('SELECT deal_id FROM crm_source WHERE source_key=?',[$key]);
            if ($existing) { return ['id'=>$existing,'duplicate'=>true]; }
            $identity=hash('sha256',$source['origin'].'|'.$source['identity']);
            $identities=[$identity];
            foreach ($source['aliases']??[] as $alias) { if (!is_string($alias) || $alias==='') { throw new \InvalidArgumentException('Identité invalide.'); } $identities[]=hash('sha256',$source['origin'].'|'.$alias); }
            $id=false;
            foreach ($identities as $candidate) { $id=$this->db->fetchOne('SELECT deal_id FROM crm_identity WHERE identity_key=?',[$candidate]); if ($id) { break; } }
            $new=!$id;
            if ($new) {
                $id=$this->create($source['fields'],$source['kind']??'private',$source['origin']);
            }
            foreach (array_unique($identities) as $identity) {
                if (!$this->db->fetchOne('SELECT deal_id FROM crm_identity WHERE identity_key=?',[$identity])) { $this->db->insert('crm_identity',['identity_key'=>$identity,'deal_id'=>$id]); }
            }
            $d=$this->get($id); $proposals=[];
            foreach ($source['fields'] as $k=>$v) {
                if ($v==='') { continue; }
                if ($d[$k]==='' && empty($d['manual_fields'][$k])) { $d[$k]=$v; }
                elseif ($d[$k]!==$v) { $proposals[$k]=$v; }
            }
            $suggested=$source['suggested_status']??null;
            if ($new && $source['origin']==='watch' && ($source['follow']??false)===true) { $d['status']='follow'; }
            elseif (isset(self::STATUSES[$suggested??'']) && $suggested!==$d['status']) { $proposals['status']=$suggested; }
            if ($source['origin']==='watch' && ($source['follow']??false)===true && $d['status']==='qualify' && empty($d['manual_fields']['status'])) { $d['status']='follow'; unset($proposals['status']); }
            $this->save($d,$d['version']);
            $this->db->insert('crm_source',['source_key'=>$key,'deal_id'=>$id]);
            $this->event($id,'source',['origin'=>$source['origin'],'title'=>$source['title'],'url'=>$source['url']??'','excerpt'=>mb_substr($source['excerpt']??'',0,12000),'extracted'=>$source['fields'],'proposals'=>$proposals,'source_key'=>$key]);
            return ['id'=>$id,'duplicate'=>false,'created'=>$new];
        });
    }
    public function hasIdentity(string $origin, string $identity): bool
    { return (bool)$this->db->fetchOne("SELECT deal_id FROM crm_identity WHERE identity_key=?",[hash("sha256",$origin."|".$identity)]); }
    public function candidates(array $deal): array
    {
        if (!$deal['email']) { return []; }
        return array_values(array_filter($this->all(),fn($d)=>$d['id']!==$deal['id'] && strtolower($d['email'])===strtolower($deal['email']) && empty($d['merged_into'])));
    }
    public function merge(string $id,string $target,string $actor): void
    {
        if ($id===$target) { throw new \InvalidArgumentException('Affaires identiques.'); }
        $this->db->transactional(function() use($id,$target,$actor) {
            $from=$this->get($id); $to=$this->get($target);
            if (!empty($from['merged_into']) || !empty($to['merged_into'])) { throw new \InvalidArgumentException('Affaire déjà rapprochée.'); }
            // Preserve both manual records, and move only future routing and history.
            foreach (['crm_source','crm_identity','crm_event'] as $t) { $this->db->update($t,['deal_id'=>$target],['deal_id'=>$id]); }
            $this->event($target,'merge',['actor'=>$actor,'original'=>$from]);
            $from['merged_into']=$target; $from['status']='archived'; $this->save($from,$from['version']);
            $this->save($to,$to['version']);
        });
    }
}

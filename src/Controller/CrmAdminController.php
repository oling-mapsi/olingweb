<?php
namespace App\Controller;

use App\Service\Crm\{CrmStore,CrmConfig,CrmDeadlines};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request,Response,StreamedResponse,JsonResponse};
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/crm')]
final class CrmAdminController extends AbstractController
{
    public function __construct(private CrmStore $store, private CrmConfig $config) {}
    private function guard(): void { $this->denyAccessUnlessGranted('ROLE_ADMIN'); }
    private function csrf(Request $r): void { $this->guard(); if (!$this->isCsrfTokenValid('crm',$r->request->get('_token'))) { throw $this->createAccessDeniedException(); } }
    #[Route('',name:'admin_crm',methods:['GET'])]
    public function index(Request $r): Response
    {
        $this->guard(); $view=$r->query->get('view','portfolio'); $q=mb_strtolower(trim($r->query->get('q',''))); $status=$r->query->get('status',''); $kind=$r->query->get('kind','');
        $segment=$r->query->get('segment',''); $origin=$r->query->get('origin',''); $due=$r->query->get('due',''); $decision=$r->query->get('decision','');
        // Old bookmarks now use the same portfolio and saved progress as the type filters.
        if (in_array($view,['markets','clients'],true)) {
            $segment=$view; $view='portfolio';
            $r->query->set('view',$view); $r->query->set('segment',$segment);
        }
        // Display scope only: the stored statuses and decisions are never rewritten.
        $closed=['won','lost','no_go','archived'];
        $scope=$r->query->get('scope',$view==='portfolio' && !$status?'active':'all');
        if (!in_array($scope,['active','closed','all'],true)) { $scope='active'; }
        $columns=array_filter(CrmStore::STATUSES,fn($key)=>$scope==='all' || ($scope==='closed')===in_array($key,$closed,true),ARRAY_FILTER_USE_KEY);
        if ($view==='todo') { $columns=array_diff_key(CrmStore::STATUSES,array_flip($closed)); }
        $all=$this->store->all();
        $rows=$this->filteredRows($all,$r);
        if ($view==='todo') { usort($rows,fn($a,$b)=>((self::agendaDate($a)??PHP_INT_MAX)<=>(self::agendaDate($b)??PHP_INT_MAX)) ?: strcmp($a['title'],$b['title']) ?: strcmp($a['id'],$b['id'])); }
        $mode=$r->query->get('mode',$view==='todo'?'list':'board')==='list'?'list':'board';
        $page=$mode==='list'?max(1,$r->query->getInt('page',1)):1;
        return $this->render('admin/crm/index.html.twig',['closed_counts'=>$this->closedCounts($all,$r),'scope'=>$scope,'columns'=>$columns,'deadlines'=>CrmDeadlines::summarize($all),'deals'=>$mode==='list'?array_slice($rows,($page-1)*50,50):$rows,'total'=>count($rows),'page'=>$page,'view'=>$view,'q'=>$q,'status'=>$status,'kind'=>$kind,'statuses'=>CrmStore::STATUSES,'origin'=>$origin,'segment'=>$segment,'due'=>$due,'decision'=>$decision,'decisions'=>CrmStore::DECISIONS,'mode'=>$mode,'connections'=>$this->config->status(),'sync'=>$this->store->state('email'),'watch'=>$this->store->state('watch'),'forms'=>$this->store->state('forms')]);
    }
    /** The closing targets replace the stage/scope filter; all other portfolio filters apply. */
    private function filteredRows(array $all, Request $r, bool $closedTotals=false): array
    {
        $view=$r->query->get('view','portfolio'); $segment=$r->query->get('segment','');
        if (in_array($view,['markets','clients'],true)) { $segment=$view; $view='portfolio'; }
        $q=mb_strtolower(trim($r->query->get('q',''))); $status=$r->query->get('status','');
        $kind=$r->query->get('kind',''); $origin=$r->query->get('origin','');
        $due=$r->query->get('due',''); $decision=$r->query->get('decision','');
        $scope=$r->query->get('scope',$view==='portfolio' && !$status?'active':'all');
        if (!in_array($scope,['active','closed','all'],true)) { $scope='active'; }
        $closed=['won','lost','no_go','archived'];
        $formsOnly=$this->config->get('client_intake','forms_only')==='forms_only';
        $now=new \DateTimeImmutable('now',new \DateTimeZone('Europe/Paris'));
        return array_values(array_filter($all,function($d) use($view,$q,$status,$kind,$origin,$due,$decision,$segment,$formsOnly,$scope,$closed,$now,$closedTotals) {
            if (!empty($d['merged_into'])) { return false; }
            if ($formsOnly && $view!=='review' && $d['origin']==='email') { return false; }
            if ($view==='review' && !$d['review_required']) { return false; }
            if ($view==='portfolio' && $d['review_required']) { return false; }
            if (!$closedTotals && $view==='portfolio' && $scope!=='all' && ($scope==='closed')!==in_array($d['status'],$closed,true)) { return false; }
            $public=$d['kind']==='public' || $d['origin']==='watch';
            if (($segment==='markets' && !$public) || ($segment==='clients' && $public)) { return false; }
            if (in_array($view,['active','todo']) && ($d['review_required'] || ($d['status']==='qualify' && !($d['origin']==='watch' && $d['decision']==='follow')))) { return false; }
            if ($origin && $origin!==$d['origin']) { return false; }
            if ($decision && $decision!==$d['decision']) { return false; }
            if (!self::matchesDue($d,$due,$now)) { return false; }
            if ($view==='qualify' && $d['status']!=='qualify') { return false; }
            if ($view==='active' && in_array($d['status'],['qualify','won','lost','no_go','archived'])) { return false; }
            if ($view==='todo' && (in_array($d['status'],['won','lost','no_go','archived']) || (!$d['next_action'] && !$d['reminder'] && !$d['deadline']))) { return false; }
            return (!$q || str_contains(mb_strtolower(implode(' ',[$d['title'],$d['organization'],$d['contact'],$d['email'],$d['reference']])),$q)) && ($closedTotals || !$status || $status===$d['status']) && (!$kind || $kind===$d['kind']);
        }));
    }
    private function closedCounts(array $all, Request $r): array
    {
        $counts=['lost'=>0,'won'=>0,'no_go'=>0];
        foreach ($this->filteredRows($all,$r,true) as $deal) {
            if (isset($counts[$deal['status']])) { ++$counts[$deal['status']]; }
        }
        return $counts;
    }
    /** Relevant dates are independent; a completed submission is not a pending action. */
    private static function agendaDates(array $deal): array
    {
        $values=[$deal['reminder']];
        if (!in_array($deal['status'],['sent','discussion','won','lost','no_go','archived'],true)) { $values[]=$deal['deadline']; }
        return array_values(array_filter(array_map(fn($value)=>$value?strtotime($value):false,$values),fn($value)=>$value!==false));
    }
    /** Chronological ordering only; never use this minimum to decide filter membership. */
    private static function agendaDate(array $deal): ?int
    {
        $dates=self::agendaDates($deal);
        return $dates?min($dates):null;
    }
    private static function matchesDue(array $deal,string $due,\DateTimeImmutable $now): bool
    {
        if ($due==='undated') { return !$deal['reminder']; }
        if (!in_array($due,['overdue','today','week'],true)) { return true; }
        foreach (self::agendaDates($deal) as $date) {
            $matches=match ($due) {
                'overdue'=>$date<$now->getTimestamp(),
                'today'=>$date>=$now->setTime(0,0)->getTimestamp() && $date<$now->modify('tomorrow')->setTime(0,0)->getTimestamp(),
                'week'=>$date>=$now->getTimestamp() && $date<=$now->modify('+7 days')->getTimestamp(),
            };
            if ($matches) { return true; }
        }
        return false;
    }
    #[Route('/{id}/delete',name:'admin_crm_delete',requirements:['id'=>'[a-f0-9]{32}'],methods:['GET','POST'])]
    public function delete(string $id,Request $r): Response
    {
        $this->guard();
        try { $deal=$this->store->get($id); } catch (\InvalidArgumentException) { throw $this->createNotFoundException(); }
        if ($r->isMethod('POST')) {
            $this->csrf($r);
            if ($r->request->get('confirm')!=='delete') { throw $this->createAccessDeniedException('Confirmation requise.'); }
            try {
                $this->store->delete($id,$r->request->getInt('version'));
                $this->addFlash('success','Fiche supprimée. Ses prochains imports seront ignorés.');
                return $this->redirectToRoute('admin_crm',['view'=>'portfolio','segment'=>$deal['kind']==='public' || $deal['origin']==='watch'?'markets':'clients']);
            } catch (\InvalidArgumentException|\RuntimeException $e) {
                $this->addFlash('danger',$e->getMessage());return $this->redirectToRoute('admin_crm_show',['id'=>$id]);
            }
        }
        return $this->render('admin/crm/delete.html.twig',['deal'=>$deal]);
    }
    #[Route('/{id}/dce',name:'admin_crm_dce',requirements:['id'=>'[a-f0-9]{32}'],methods:['POST'])]
    public function dce(string $id,Request $r): JsonResponse
    {
        $this->csrf($r);
        try {
            $value=$r->request->get('received');
            if (!in_array($value,['0','1'],true)) { throw new \InvalidArgumentException('État DCE invalide.'); }
            $this->store->setDceReceived($id,$value==='1',$r->request->getInt('version'),$this->getUser()->getUserIdentifier());
            $d=$this->store->get($id);
            return $this->json(['saved'=>true,'version'=>$d['version'],'dce_received'=>$d['dce_received']]);
        } catch (\InvalidArgumentException|\RuntimeException $e) { return $this->json(['error'=>$e->getMessage()],409); }
    }
    #[Route('/{id}/cc',name:'admin_crm_cc',requirements:['id'=>'[a-f0-9]{32}'],methods:['POST'])]
    public function cc(string $id,Request $r): JsonResponse
    {
        $this->csrf($r);
        try {
            $value=$r->request->get('received');
            if (!in_array($value,['0','1'],true)) { throw new \InvalidArgumentException('État CC invalide.'); }
            $this->store->setCcReceived($id,$value==='1',$r->request->getInt('version'),$this->getUser()->getUserIdentifier());
            $d=$this->store->get($id);
            return $this->json(['saved'=>true,'version'=>$d['version'],'cc_received'=>$d['cc_received']]);
        } catch (\InvalidArgumentException|\RuntimeException $e) { return $this->json(['error'=>$e->getMessage()],409); }
    }
    #[Route('/{id}/move',name:'admin_crm_move',requirements:['id'=>'[a-f0-9]{32}'],methods:['POST'])]
    public function move(string $id,Request $r): JsonResponse
    {
        $this->csrf($r);
        try {
            $d=$this->store->get($id);
            if ($r->request->has('decision')) {
                $this->store->decide($id,$r->request->get('decision',''),$r->request->getInt('version'),$this->getUser()->getUserIdentifier());
                return $this->json(['saved'=>true,'version'=>$this->store->get($id)['version'],'deadline_html'=>$this->renderView('admin/crm/_deadlines.html.twig',['deadlines'=>CrmDeadlines::summarize($this->store->all())])]);
            }
            $this->store->edit($id,[],$r->request->get('status',''),$d['kind'],'',$this->getUser()->getUserIdentifier(),$r->request->getInt('version'));
            $updated=$this->store->get($id); $dueMatches=[];
            $now=new \DateTimeImmutable('now',new \DateTimeZone('Europe/Paris'));
            foreach (['overdue','today','week','undated'] as $due) { $dueMatches[$due]=self::matchesDue($updated,$due,$now); }
            return $this->json(['saved'=>true,'closed_counts'=>$this->closedCounts($this->store->all(),$r),'due_matches'=>$dueMatches,'card_html'=>$this->renderView('admin/crm/_card.html.twig',['d'=>$updated,'statuses'=>CrmStore::STATUSES]),'version'=>$updated['version'],'deadline_html'=>$this->renderView('admin/crm/_deadlines.html.twig',['deadlines'=>CrmDeadlines::summarize($this->store->all())])]);
        } catch (\InvalidArgumentException|\RuntimeException $e) { return $this->json(['error'=>$e->getMessage()],409); }
    }
    #[Route('/new',name:'admin_crm_new',methods:['GET','POST'])]
    public function create(Request $r): Response
    {
        $this->guard();
        if ($r->isMethod('POST')) {
            $this->csrf($r);
            try { $id=$this->store->create($r->request->all('fields'),$r->request->get('kind','private')); $this->store->event($id,'manual',['actor'=>$this->getUser()->getUserIdentifier(),'note'=>'Affaire créée manuellement.']); return $this->redirectToRoute('admin_crm_show',['id'=>$id]); }
            catch (\InvalidArgumentException $e) { $this->addFlash('danger',$e->getMessage()); }
        }
        return $this->render('admin/crm/new.html.twig',['values'=>$r->request->all('fields')]);
    }
    #[Route('/export.csv',name:'admin_crm_export',methods:['GET'])]
    public function export(): Response
    {
        $this->guard(); $rows=$this->store->all();
        $r=new StreamedResponse(function() use($rows) {
            $out=fopen('php://output','w'); fwrite($out,"\xEF\xBB\xBF"); $columns=['id','status','kind',...CrmStore::FIELDS,'origin','updated_at']; fputcsv($out,$columns,';');
            foreach($rows as $d) { $values=[]; foreach($columns as $c) { $v=(string)($d[$c]??''); $values[]=preg_match('/^[\s]*[=+@\-\t\r]/u',$v)?"'".$v:$v; } fputcsv($out,$values,';'); } fclose($out);
        });
        $r->headers->set('Content-Type','text/csv; charset=UTF-8'); $r->headers->set('Content-Disposition','attachment; filename="oling-crm.csv"'); $r->headers->set('Cache-Control','private, no-store'); return $r;
    }
    #[Route('/sync',name:'admin_crm_sync',methods:['POST'])]
    public function sync(Request $r): Response
    {
        $this->csrf($r);
        if ($this->config->graphReady() && $this->config->enabled('scheduler')) { $this->store->setState('sync_request',['at'=>CrmStore::now()]); $this->addFlash('success','Synchronisation demandée, prise en charge sous cinq minutes.'); }
        else { $this->addFlash('warning','Microsoft 365 ou la tâche serveur n’est pas activé.'); }
        return $this->redirectToRoute('admin_crm');
    }
    #[Route('/{id}',name:'admin_crm_show',requirements:['id'=>'[a-f0-9]{32}'],methods:['GET','POST'])]
    public function show(string $id,Request $r): Response
    {
        $this->guard(); try { $deal=$this->store->get($id); } catch (\InvalidArgumentException) { throw $this->createNotFoundException(); }
        if ($r->isMethod('POST')) {
            $this->csrf($r);
            try {
                $action=$r->request->get('action','save'); $fields=$r->request->all('fields'); $status=$r->request->get('status',$deal['status']);
                if ($action==='decision') { $this->store->decide($id,$r->request->get('decision',''),$r->request->getInt('version'),$this->getUser()->getUserIdentifier()); return $this->redirectToRoute('admin_crm_show',['id'=>$id]); }
                if ($action==='qualify') { $this->store->qualify($id,$r->request->getInt('version'),$this->getUser()->getUserIdentifier()); return $this->redirectToRoute('admin_crm_show',['id'=>$id]); }
                if ($action==='merge') { $this->store->merge($id,$r->request->get('target',''),$this->getUser()->getUserIdentifier()); return $this->redirectToRoute('admin_crm_show',['id'=>$r->request->get('target')]); }
                if (isset(CrmStore::STATUSES[$action])) { $status=$action; $fields=[]; }
                if ($action==='remind') { $fields=['reminder'=>$r->request->get('reminder',''),'next_action'=>$r->request->get('next_action','Relancer')]; }
                if ($action==='proposal') {
                    $event=null; foreach($this->store->history($id) as $e) { if ($e['id']===$r->request->get('event')) { $event=$e; break; } }
                    $key=$r->request->get('field');
                    if (!$event || !array_key_exists($key,$event['data']['proposals']??[])) { throw new \InvalidArgumentException('Proposition introuvable.'); }
                    $value=$event['data']['proposals'][$key]; $fields=$key==='status'?[]:[$key=>$value]; if($key==='status') { $status=$value; }
                }
                $this->store->edit($id,$fields,$status,$r->request->get('kind',$deal['kind']),$r->request->get('note',''),$this->getUser()->getUserIdentifier(),$r->request->getInt('version'));
                $this->addFlash('success','Affaire mise à jour.'); return $this->redirectToRoute('admin_crm_show',['id'=>$id]);
            } catch (\InvalidArgumentException|\RuntimeException $e) { $this->addFlash('danger',$e->getMessage()); return $this->redirectToRoute('admin_crm_show',['id'=>$id]); }
        }
        return $this->render('admin/crm/show.html.twig',['deal'=>$deal,'history'=>$this->store->history($id),'candidates'=>$this->store->candidates($deal),'statuses'=>CrmStore::STATUSES,'decisions'=>CrmStore::DECISIONS]);
    }
}

<?php
namespace App\Controller;

use App\Service\Crm\{CrmStore,CrmConfig};
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
        $rows=array_values(array_filter($this->store->all(),function($d) use($view,$q,$status,$kind,$origin,$due,$decision,$segment) {
            if (!empty($d['merged_into'])) { return false; }
            if ($view==='markets' && $d['origin']!=='watch') { return false; }
            if ($view==='clients' && ($d['origin']==='watch' || $d['review_required'])) { return false; }
            if ($view==='review' && !$d['review_required']) { return false; }
            if ($view==='portfolio' && $d['review_required']) { return false; }
            $public=$d['kind']==='public' || $d['origin']==='watch';
            if (($segment==='markets' && !$public) || ($segment==='clients' && $public)) { return false; }
            if (in_array($view,['active','todo']) && ($d['review_required'] || ($d['origin']==='watch' ? $d['decision']!=='follow' : $d['status']==='qualify'))) { return false; }
            if ($origin && $origin!==$d['origin']) { return false; }
            if ($decision && $decision!==$d['decision']) { return false; }
            $date=$d['reminder']?:$d['deadline'];
            if ($due==='overdue' && (!$date || $date>=CrmStore::now())) { return false; }
            if ($due==='week' && (!$date || $date>gmdate('Y-m-d\\TH:i:s\\Z',time()+7*86400))) { return false; }
            if ($view==='qualify' && $d['status']!=='qualify') { return false; }
            if ($view==='active' && in_array($d['status'],['qualify','won','lost','no_go','archived'])) { return false; }
            if ($view==='todo' && (in_array($d['status'],['won','lost','no_go','archived']) || (!$d['next_action'] && !$d['reminder'] && !$d['deadline']))) { return false; }
            return (!$q || str_contains(mb_strtolower(implode(' ',[$d['title'],$d['organization'],$d['contact'],$d['email'],$d['reference']])),$q)) && (!$status || $status===$d['status']) && (!$kind || $kind===$d['kind']);
        }));
        if ($view==='todo') { usort($rows,fn($a,$b)=>strcmp($a['reminder']?:$a['deadline']?:'9999',$b['reminder']?:$b['deadline']?:'9999')); }
        $mode=$r->query->get('mode','board')==='list'?'list':'board';
        $page=$mode==='list'?max(1,$r->query->getInt('page',1)):1;
        return $this->render('admin/crm/index.html.twig',['deals'=>$mode==='list'?array_slice($rows,($page-1)*50,50):$rows,'total'=>count($rows),'page'=>$page,'view'=>$view,'q'=>$q,'status'=>$status,'kind'=>$kind,'statuses'=>CrmStore::STATUSES,'origin'=>$origin,'segment'=>$segment,'due'=>$due,'decision'=>$decision,'decisions'=>CrmStore::DECISIONS,'mode'=>$mode,'connections'=>$this->config->status(),'sync'=>$this->store->state('email'),'watch'=>$this->store->state('watch'),'forms'=>$this->store->state('forms')]);
    }
    #[Route('/{id}/move',name:'admin_crm_move',requirements:['id'=>'[a-f0-9]{32}'],methods:['POST'])]
    public function move(string $id,Request $r): JsonResponse
    {
        $this->csrf($r);
        try {
            $d=$this->store->get($id);
            if ($r->request->has('decision')) {
                $this->store->decide($id,$r->request->get('decision',''),$r->request->getInt('version'),$this->getUser()->getUserIdentifier());
                return $this->json(['saved'=>true,'version'=>$this->store->get($id)['version']]);
            }
            $this->store->edit($id,[],$r->request->get('status',''),$d['kind'],'',$this->getUser()->getUserIdentifier(),$r->request->getInt('version'));
            return $this->json(['saved'=>true,'version'=>$this->store->get($id)['version']]);
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

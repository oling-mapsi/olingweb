<?php
namespace App\Controller\Ai;
use App\Service\Ai\{AiStore,AiWorkflow,AiArtifacts,AiNativeRegistry};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request,Response,BinaryFileResponse};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
final class AiController extends AbstractController
{
    public function __construct(private AiStore $store,private AiArtifacts $artifacts,private AiNativeRegistry $native) {}
    private function owner(): int {$this->denyAccessUnlessGranted('ROLE_ADMIN');return $this->getUser()->getId();}
    private function csrf(Request $r): void {if(!$this->isCsrfTokenValid('ai_control',$r->request->get('_token')))throw $this->createAccessDeniedException('Formulaire expiré.');}
    #[Route('/ai/login',name:'admin_ai_login',methods:['GET','POST'])]
    public function login(AuthenticationUtils $auth): Response
    {if($this->isGranted('ROLE_ADMIN'))return $this->redirectToRoute('admin_ai');return $this->render('ai_control/login.html.twig',['last_username'=>$auth->getLastUsername(),'error'=>$auth->getLastAuthenticationError()]);}
    #[Route('/ai/logout',name:'admin_ai_logout',methods:['POST'])]
    public function logout(): never {throw new \LogicException('Handled by AI firewall.');}
    #[Route('/admin/ai',name:'admin_ai',methods:['GET'])]
    public function index(Request $r): Response
    {
        $owner=$this->owner();$view=$r->query->get('view','home');if(!in_array($view,['home','missions','assets','prompts'],true))throw $this->createNotFoundException();
        return $this->render('ai_control/index.html.twig',['view'=>$view,'missions'=>$this->store->all($owner),'prompts'=>$view==='prompts'?$this->store->prompts($owner):[],'assets'=>$view==='assets'?$this->store->assets($owner):[],'states'=>AiNativeRegistry::STATES+AiWorkflow::STATES,'request_key'=>bin2hex(random_bytes(16))]);
    }
    #[Route('/admin/ai/missions',name:'admin_ai_create',methods:['POST'])]
    public function create(Request $r): Response
    {
        $owner=$this->owner();$this->csrf($r);
        try {$result=$this->native->call('ai_mission_create',['title'=>$r->request->get('title',''),'brief'=>$r->request->get('brief',''),'project'=>$r->request->get('project',''),'event_id'=>$r->request->get('request_key','')],$owner,'human-'.$owner);$id=$result['mission_id'];return $this->redirectToRoute('admin_ai_mission',['id'=>$id]);}
        catch(\InvalidArgumentException){return new Response('Demande invalide ou contenu sensible détecté.',422);} catch(\DomainException){return new Response('Demande déjà modifiée ou identifiant réutilisé.',409);}
    }
    #[Route('/admin/ai/missions/{id}',name:'admin_ai_mission',requirements:['id'=>'[a-f0-9]{32}'],methods:['GET','POST'])]
    public function mission(string $id,Request $r): Response
    {
        $owner=$this->owner();
        try {
            if($r->isMethod('POST')) {
                $this->csrf($r);$version=$r->request->getInt('version');$key=$r->request->get('request_key','');$action=$r->request->get('action');
                if($action==='prompt')$this->native->call('ai_mission_prompt',['mission_id'=>$id,'version'=>$version,'event_id'=>$key,'content'=>$r->request->get('content','')],$owner,'human-'.$owner);
                else $this->native->human($id,$owner,$version,$key,$action==='review'?$r->request->get('decision',''):(string)$action,$r->request->get('feedback',''),$r->request->get('verified')==='1');
                return $this->redirectToRoute('admin_ai_mission',['id'=>$id]);
            }
            $data=$this->native->detail($id,$owner);
            return $this->render('ai_control/mission.html.twig',$data+['view'=>'missions','states'=>AiNativeRegistry::STATES+AiWorkflow::STATES,'request_key'=>bin2hex(random_bytes(16))]);
        } catch(\OutOfBoundsException){throw $this->createNotFoundException();}
        catch(\DomainException $e){return new Response($e->getMessage(),409);}
        catch(\InvalidArgumentException $e){return new Response($e->getMessage(),422);}
    }
    #[Route('/admin/ai/assets/{id}',name:'admin_ai_asset',requirements:['id'=>'[a-f0-9]{32}'],methods:['GET'])]
    public function download(string $id): Response
    {
        $owner=$this->owner();try {$asset=$this->store->asset($id,$owner);$response=new BinaryFileResponse($this->artifacts->path($asset));$response->setContentDisposition('attachment','preuve-'.$id.'.txt');$response->headers->set('Content-Type','text/plain; charset=UTF-8');return $response;}
        catch(\OutOfBoundsException|\RuntimeException){throw $this->createNotFoundException();}
    }
}

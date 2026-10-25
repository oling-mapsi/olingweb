<?php
// Read-only GET checks against a staged application. No form submission or CRM writes.
$root=$argv[1];$out=$argv[2];
require $root.'/vendor/autoload.php';
(new Symfony\Component\Dotenv\Dotenv())->bootEnv($root.'/.env');
$paths=['/facturation-electronique-amoa','/crm','/consulting/amoa-ia-pilotage-de-projets-ia-et-agents-metier','/expertises/amoa-ia-pilotage-projets-agents','/expertises/rgpd-dpo-gouvernance','/erp-progiciel','/erp-progiciel/questionnaire','/contact?need=crm&source_path=%2Fcrm'];
$paths=array_merge($paths,['/en/e-invoicing-project-advisory','/es/asesoria-facturacion-electronica','/en/crm-advisory','/es/asesoria-crm','/en/outsourced-dpo-ongoing-gdpr-governance','/es/dpo-externo-gobernanza-rgpd-continua','/en/ai-project-advisory-programme-management-business-agents','/es/asesoria-ia-gestion-proyectos-agentes-negocio','/en/services/ai-project-advisory-management-and-business-agents','/es/servicios/asesoria-ia-gestion-de-proyectos-ia-y-agentes-de-negocio','/en/erp-software','/es/asesoria-erp-software-gestion']);
$rows=[];
foreach($paths as $path) {
    $kernel=new App\Kernel('prod',false);$kernel->boot();
    $request=Symfony\Component\HttpFoundation\Request::create('https://oling.fr'.$path);
    $response=$kernel->handle($request);$html=$response->getContent();
    if($response->getStatusCode()!==200)throw new RuntimeException('Unexpected HTTP status for '.$path.': '.$response->getStatusCode());
    $erp=App\Service\QualificationContext::allowsErp($request->getPathInfo());
    foreach(['data-chat-erp-form','data-erp-questionnaire-url-template'] as $marker) {
        if(str_contains($html,$marker)!==$erp)throw new RuntimeException('ERP context mismatch: '.$path.' '.$marker);
    }
    if(!$erp && preg_match('/Questionnaire ERP|Qualifier un besoin ERP|Lancer le questionnaire ERP|\/erp-progiciel\/questionnaire|erp-questionnaire/',$html))throw new RuntimeException('ERP leak: '.$path);
    $file=str_replace('/','_',ltrim($request->getPathInfo(),'/')).'.html';
    file_put_contents($out.'/'.$file,$html);
    $rows[]=['path'=>$path,'http'=>$response->getStatusCode(),'route'=>$request->attributes->get('_route'),'controller'=>$request->attributes->get('_controller'),'slug'=>$request->attributes->get('slug'),'erp'=>$erp];
    $kernel->shutdown();
}
file_put_contents($out.'/render-proof.json',json_encode($rows,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
echo json_encode($rows,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;

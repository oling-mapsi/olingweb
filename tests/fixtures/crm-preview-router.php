<?php
// Static fixture preview ONLY. No application boot, credentials or real endpoints.
$root=dirname(__DIR__,2);
$routes=['/index.html'=>$root.'/var/closing-preview/index.html','/'=>$root.'/var/closing-preview/index.html','/tests.html'=>__DIR__.'/crm-closing-browser.html','/crm/workspace.js'=>$root.'/public/crm/workspace.js','/crm/workspace.css'=>$root.'/public/crm/workspace.css','/crm/oling-logo.png'=>$root.'/public/crm/oling-logo.png'];
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
if ($_SERVER['REQUEST_METHOD']!=='GET' || !isset($routes[$path])) { http_response_code(404); echo 'Aperçu statique : action indisponible.'; return; }
header('Content-Type: '.match(pathinfo($routes[$path],PATHINFO_EXTENSION)) {'css'=>'text/css','js'=>'text/javascript','png'=>'image/png',default=>'text/html; charset=UTF-8'});
readfile($routes[$path]);

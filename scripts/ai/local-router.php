<?php
if(PHP_SAPI!=='cli-server'||!in_array($_SERVER['REMOTE_ADDR']??'',['127.0.0.1','::1'],true)){http_response_code(403);exit;}
require __DIR__.'/bootstrap-local.php';
$_ENV['DATABASE_URL']=$_SERVER['DATABASE_URL']='sqlite:///'.dirname(__DIR__,2).'/var/ai-fixture.sqlite';
// Native session storage in dev, synthetic configuration only. No .env/bootstrap/runtime loader.
$_ENV['APP_ENV']=$_SERVER['APP_ENV']='dev';
$kernel=new App\Kernel('dev',false);
$request=Symfony\Component\HttpFoundation\Request::createFromGlobals();
$response=$kernel->handle($request);$response->send();$kernel->terminate($request,$response);

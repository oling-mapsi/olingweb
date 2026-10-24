<?php
// Synthetic local-only bootstrap. Never loads .env files, production DSNs or connector settings.
$root=dirname(__DIR__,2);
$vendor=getenv('AI_TEST_VENDOR')?:$root.'/vendor';
$loader=require $vendor.'/autoload.php';
$loader->addPsr4('App\\',$root.'/src/',true);
$loader->addPsr4('App\\Tests\\',$root.'/tests/',true);
foreach($loader->getClassMap() as $class=>$file)if(str_starts_with($class,'App\\')) {
    $suffix=str_replace('\\','/',substr($class,4));
    $target=str_starts_with($suffix,'Tests/')?$root.'/tests/'.substr($suffix,6).'.php':$root.'/src/'.$suffix.'.php';
    if(is_file($target))$loader->addClassMap([$class=>$target]);
}
foreach(['APP_ENV'=>'test','APP_DEBUG'=>'1','APP_SECRET'=>'synthetic-ai-test-only-not-a-production-secret','KERNEL_CLASS'=>'App\\Kernel','DATABASE_URL'=>'sqlite:///:memory:','MAILER_DSN'=>'null://null','MESSENGER_TRANSPORT_DSN'=>'in-memory://','LOCK_DSN'=>'flock','GA4_PROPERTY_ID'=>'','GA4_CREDENTIALS_PATH'=>'','OPENAI_API_KEY'=>'','MISTRAL_API_KEY'=>'','CHAT_AI_PROVIDER'=>'heuristic','GROWTH_FACTORY_ENABLED'=>'0','AI_RELAY_ENABLED'=>'0','TWILIO_DSN'=>'null://null','NEXMO_DSN'=>'null://null','TELEGRAM_DSN'=>'null://null','SLACK_DSN'=>'null://null'] as $key=>$value) {
    $_ENV[$key]=$_SERVER[$key]=$value;putenv($key.'='.$value);
}

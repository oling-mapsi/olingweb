<?php
// Isolated fixtures: never boot Dotenv or use production services/credentials.
$root=dirname(__DIR__,2);
$loader=require (getenv('CRM_TEST_AUTOLOAD') ?: $root.'/vendor/autoload.php');
$loader->setPsr4('App\\',[$root.'/src']);
$loader->setPsr4('App\\Tests\\',[$root.'/tests']);
foreach (['APP_ENV'=>'test','APP_DEBUG'=>'0','APP_SECRET'=>'synthetic-fixture-only','KERNEL_CLASS'=>'App\\Kernel','DATABASE_URL'=>'sqlite:///:memory:','MAILER_DSN'=>'null://null','MESSENGER_TRANSPORT_DSN'=>'in-memory://','LOCK_DSN'=>'flock','CHAT_AI_PROVIDER'=>'disabled','OPENAI_API_KEY'=>'','MISTRAL_API_KEY'=>'','GROWTH_FACTORY_ENABLED'=>'0','GA4_PROPERTY_ID'=>'','GA4_CREDENTIALS_PATH'=>''] as $key=>$value) {
    $_ENV[$key]=$_SERVER[$key]=$value;
}

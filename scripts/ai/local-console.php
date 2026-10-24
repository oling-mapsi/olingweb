<?php
require __DIR__.'/bootstrap-local.php';
$kernel=new App\Kernel('test',false);
$application=new Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
$application->run();

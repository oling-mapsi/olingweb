<?php
require dirname(__DIR__).'/ai/bootstrap-local.php';
$application=new Symfony\Bundle\FrameworkBundle\Console\Application(new App\Kernel('test',false));
$application->setAutoExit(false);
exit($application->run(new Symfony\Component\Console\Input\ArrayInput(['command'=>'lint:twig','filename'=>['templates/includes/_chat_widget_v2.html.twig','templates/seo/_db-landing.html.twig','templates/public/_page_hero.html.twig','templates/public/_cta_band.html.twig']])));

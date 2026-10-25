<?php
namespace App\Tests;

use App\Entity\{SitePage,SitePageTranslation,Practice,Services};
use App\Service\QualificationContext;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class QualificationContextTest extends WebTestCase
{
    public const PAGES = [
        '/facturation-electronique-amoa'=>'rfe', '/crm'=>'crm',
        '/consulting/amoa-ia-pilotage-de-projets-ia-et-agents-metier'=>'amoa-ia',
        '/expertises/amoa-ia-pilotage-projets-agents'=>'amoa-ia',
        '/expertises/rgpd-dpo-gouvernance'=>'dpo',
    ];

    public function testRenderedJourneysAndErpPositiveControl(): void
    {
        $client=self::createClient();$client->disableReboot();$client->catchExceptions(false);
        $em=self::getContainer()->get('doctrine')->getManager();
        (new SchemaTool($em))->createSchema($em->getMetadataFactory()->getAllMetadata());
        foreach(['crm','facturation-electronique-amoa','erp-progiciel','expertise-amoa-ia-pilotage-projets-agents','expertise-rgpd-dpo-gouvernance'] as $slug) {
            $page=(new SitePage())->setSlug($slug)->setTitle('Fixture '.$slug)->setHeroTitle('Fixture')->setBodyHtml(json_encode(['title'=>'Fixture','situations'=>[],'interventions'=>[],'deliverables'=>[],'serviceLinks'=>[],'proofLinks'=>[],'linkedServices'=>[]]));
            $translation=(new SitePageTranslation())->setLocale('fr')->setSlug($slug)->setTitle($page->getTitle())->setHeroTitle('Fixture')->setBodyHtml($page->getBodyHtml())->setTranslationStatus('published');
            $page->addTranslation($translation);$em->persist($translation);$em->persist($page);
        }
        $practice=(new Practice())->setDesignation('Consulting')->setImage1('fixture.jpg');
        (new \ReflectionProperty($practice,'slug'))->setValue($practice,'consulting');
        $service=(new Services())->setDesignation('AMOA IA')->setImage1('fixture.jpg')->setIntroductionShort('Cadrer les usages IA')->setPractice($practice);
        (new \ReflectionProperty($service,'slug'))->setValue($service,'amoa-ia-pilotage-de-projets-ia-et-agents-metier');
        $em->persist($practice);$em->persist($service);$em->flush();
        $db=$em->getConnection();
        $db->executeStatement('CREATE TABLE practice_translation (practice_id INTEGER, locale TEXT, slug TEXT, designation TEXT, translation_status TEXT)');
        $db->insert('practice_translation',['practice_id'=>$practice->getId(),'locale'=>'fr','slug'=>'consulting','designation'=>'Consulting','translation_status'=>'published']);
        $db->executeStatement('CREATE TABLE service_translation (service_id INTEGER, locale TEXT, slug TEXT, designation TEXT, introduction_short TEXT, public_narrative TEXT, translation_status TEXT)');
        $db->insert('service_translation',['service_id'=>$service->getId(),'locale'=>'fr','slug'=>$service->getSlug(),'designation'=>'AMOA IA','introduction_short'=>'Cadrer les usages IA','public_narrative'=>json_encode(['eyebrow'=>'IA','headline'=>'AMOA IA','intro'=>'Cas d’usage','outcomes'=>[]]),'translation_status'=>'published']);
        foreach(self::PAGES as $url=>$need) {
            $crawler=$client->request('GET',$url);self::assertResponseIsSuccessful();
            $html=$client->getResponse()->getContent();
            foreach(['data-chat-erp-form','erp-questionnaire','Questionnaire ERP','Qualifier un besoin ERP','Lancer le questionnaire ERP','/erp-progiciel/questionnaire'] as $forbidden) self::assertStringNotContainsString($forbidden,$html,$url);
            self::assertSelectorExists('#oling-chat-widget[data-qualification-context="'.$need.'"]');
            self::assertStringNotContainsString('qualification.prefill.', $crawler->filter('#oling-chat-widget')->attr('data-qualification-prefill'));
            $ctas=$crawler->filter('main a.btn[href*="source_path="]');self::assertGreaterThan(0,$ctas->count(),$url);
            parse_str(parse_url($ctas->first()->attr('href'),PHP_URL_QUERY),$query);
            self::assertSame($url,$query['source_path']);self::assertSame($need,$query['need']);
        }
        $client->request('GET','/erp-progiciel');self::assertResponseIsSuccessful();
        self::assertSelectorExists('[data-chat-erp-form]');self::assertSelectorExists('a[href="/erp-progiciel/questionnaire"]');
        $client->request('GET','/erp-progiciel/questionnaire');self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
    }

    public function testExplicitPolicyAndNeutralFallback(): void
    {
        foreach(self::PAGES as $path=>$need) { self::assertSame($need,QualificationContext::need($path.'/'));self::assertFalse(QualificationContext::allowsErp($path)); }
        foreach(['/','/anything-erp','/crm?erp=1','/erp-progiciel/unknown'] as $path) self::assertFalse(QualificationContext::allowsErp($path));
        foreach(['/erp-progiciel','/business-apps/erp','/en/erp-software/questionnaire','/es/software-erp/cuestionario'] as $path) self::assertTrue(QualificationContext::allowsErp($path));
    }
}

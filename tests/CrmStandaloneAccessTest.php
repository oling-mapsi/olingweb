<?php
namespace App\Tests;

use App\Entity\User;
use App\Service\Crm\CrmSchema;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CrmStandaloneAccessTest extends WebTestCase
{
    public function testSeparateLoginUsesExistingAccountsWithoutAdminNavigation(): void
    {
        $_ENV['DATABASE_URL']=$_SERVER['DATABASE_URL']='sqlite:///:memory:';
        $_ENV['MAILER_DSN']=$_SERVER['MAILER_DSN']='null://null';
        $client=self::createClient(); $client->disableReboot();
        $em=self::getContainer()->get('doctrine')->getManager();
        (new SchemaTool($em))->createSchema($em->getMetadataFactory()->getAllMetadata());
        $s=new Schema(); CrmSchema::add($s);
        foreach ($s->toSql($em->getConnection()->getDatabasePlatform()) as $sql) { $em->getConnection()->executeStatement($sql); }
        $u=(new User())->setUsername('shared-account')->setEmail('shared@example.invalid')->setRoles(['ROLE_ADMIN']);
        $hash=self::getContainer()->get('security.user_password_hasher')->hashPassword($u,'Synthetic-password-42');
        $u->setPassword($hash); $em->persist($u); $em->flush();

        $client->request('GET','https://oling.fr/admin/crm'); self::assertResponseRedirects('/login');
        $client->request('GET','https://crm.oling.fr/'); self::assertResponseRedirects('/admin/crm');
        $client->request('GET','https://crm.oling.fr/admin/crm'); self::assertResponseRedirects('/crm/login');
        $c=$client->followRedirect(); self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1','Votre espace commercial');
        self::assertSelectorNotExists('a[href*="oling.fr/admin"]');
        self::assertNotNull($client->getCookieJar()->get('OLINGCRMSESSID','/','crm.oling.fr'));
        $client->request('POST','https://crm.oling.fr/crm/login',['_username'=>'shared-account','_password'=>'Synthetic-password-42','_csrf_token'=>'invalid']);
        self::assertResponseRedirects('/crm/login');
        $c=$client->followRedirect(); self::assertSelectorExists('[role="alert"]');
        $client->submit($c->selectButton('Se connecter')->form(),['_username'=>'shared-account','_password'=>'Synthetic-password-42']);
        self::assertResponseRedirects('/admin/crm');
        $client->followRedirect(); self::assertResponseIsSuccessful();
        self::assertSelectorCount(5,'.lane'); self::assertSelectorExists('.app-header .portfolio-filters');
        self::assertSelectorExists('.workspace-heading #deadline-summary');
        self::assertSelectorNotExists('.admin-link');
        self::assertSame($hash,$em->find(User::class,$u->getId())->getPassword());
        $client->request('GET','https://crm.oling.fr/admin'); self::assertResponseRedirects('/admin/crm');
        $client->request('GET','https://crm.oling.fr/admin/users'); self::assertResponseStatusCodeSame(404);
        $client->request('GET','https://crm.oling.fr/contact'); self::assertResponseStatusCodeSame(404);
        $client->request('GET','https://crm.oling.fr/login'); self::assertResponseRedirects('/crm/login');
        $client->request('GET','https://oling.fr/admin/crm'); self::assertResponseRedirects('/login');
        $c=$client->request('GET','https://crm.oling.fr/admin/crm'); self::assertResponseIsSuccessful();
        $logout=$c->selectButton('Déconnexion')->form();
        $client->request('POST','https://crm.oling.fr/crm/logout',['_csrf_token'=>'invalid']); self::assertResponseStatusCodeSame(403);
        $client->submit($logout); self::assertResponseRedirects('/crm/login');
        $client->request('GET','https://crm.oling.fr/admin/crm'); self::assertResponseRedirects('/crm/login');
        // The original admin login still accepts the same account and retains its target.
        $c=$client->request('GET','https://oling.fr/login'); self::assertResponseIsSuccessful();
        $form=$c->filter('form[method="post"]')->form();
        $client->submit($form,['_username'=>'shared-account','_password'=>'Synthetic-password-42']);
        self::assertResponseRedirects('/admin');
        $client->request('GET','https://crm.oling.fr/admin/crm'); self::assertResponseRedirects('/crm/login');
    }
}

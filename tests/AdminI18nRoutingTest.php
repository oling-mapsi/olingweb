<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class AdminI18nRoutingTest extends KernelTestCase
{
    public function testAdminTranslationsRoutesAreRegistered(): void
    {
        $_SERVER['DATABASE_URL'] ??= 'sqlite:///:memory:';
        $_ENV['DATABASE_URL'] ??= 'sqlite:///:memory:';
        self::bootKernel();
        $routes = self::getContainer()->get('router')->getRouteCollection();

        self::assertNotNull($routes->get('admin_translations_index'));
        self::assertSame('/admin/translations', $routes->get('admin_translations_index')?->getPath());
        self::assertNotNull($routes->get('admin_translations_site_page_edit'));
        self::assertNotNull($routes->get('admin_translations_site_page_generate'));
        self::assertNotNull($routes->get('admin_translations_site_page_preview'));
        self::assertNotNull($routes->get('admin_translations_bulk_generate'));
    }

    public function testAdminMenuAndPagesExposeTranslationEntryPoints(): void
    {
        $projectDir = dirname(__DIR__);
        $nav = file_get_contents($projectDir.'/templates/admin/_nav.html.twig');
        $pagesIndex = file_get_contents($projectDir.'/templates/admin/pages/index.html.twig');
        $pagesEdit = file_get_contents($projectDir.'/templates/admin/pages/edit.html.twig');

        self::assertStringContainsString('admin_translations_index', (string) $nav);
        self::assertStringContainsString('Traductions', (string) $nav);
        self::assertStringContainsString('Gérer les traductions', (string) $pagesIndex);
        self::assertStringContainsString('Français', (string) $pagesEdit);
        self::assertStringContainsString('English', (string) $pagesEdit);
        self::assertStringContainsString('Español', (string) $pagesEdit);
        self::assertStringContainsString("Générer avec l'IA", (string) $pagesEdit);
    }
}

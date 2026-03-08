<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SitemapFallbackController extends AbstractController
{
    #[Route('/sitemap.xml', name: 'sitemap_fallback_index', priority: 1000)]
    public function sitemapIndex(): Response
    {
        return $this->serveXmlFileOrForward('sitemap.xml');
    }

    #[Route('/sitemap.default.xml', name: 'sitemap_fallback_default', priority: 1000)]
    public function sitemapDefault(): Response
    {
        return $this->serveXmlFileOrForward('sitemap.default.xml', 'default');
    }

    #[Route('/sitemap.practice.xml', name: 'sitemap_fallback_practice', priority: 1000)]
    public function sitemapPractice(): Response
    {
        return $this->serveXmlFileOrForward('sitemap.practice.xml', 'practice');
    }

    #[Route('/sitemap.services.xml', name: 'sitemap_fallback_services', priority: 1000)]
    public function sitemapServices(): Response
    {
        return $this->serveXmlFileOrForward('sitemap.services.xml', 'services');
    }

    private function serveXmlFileOrForward(string $filename, ?string $section = null): Response
    {
        $path = $this->getParameter('kernel.project_dir') . '/public/' . $filename;

        if (is_file($path)) {
            return new Response(
                (string) file_get_contents($path),
                Response::HTTP_OK,
                ['Content-Type' => 'application/xml; charset=UTF-8']
            );
        }

        if ($section === null) {
            return $this->forward('Presta\\SitemapBundle\\Controller\\SitemapController::indexAction');
        }

        return $this->forward('Presta\\SitemapBundle\\Controller\\SitemapController::sectionAction', [
            'name' => $section,
        ]);
    }
}

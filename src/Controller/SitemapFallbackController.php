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
        return $this->serveXmlFile('sitemap.xml');
    }

    #[Route('/sitemap.default.xml', name: 'sitemap_fallback_default', priority: 1000)]
    public function sitemapDefault(): Response
    {
        return $this->serveXmlFile('sitemap.default.xml');
    }

    #[Route('/sitemap.practice.xml', name: 'sitemap_fallback_practice', priority: 1000)]
    public function sitemapPractice(): Response
    {
        return $this->serveXmlFile('sitemap.practice.xml');
    }

    #[Route('/sitemap.services.xml', name: 'sitemap_fallback_services', priority: 1000)]
    public function sitemapServices(): Response
    {
        return $this->serveXmlFile('sitemap.services.xml');
    }

    private function serveXmlFile(string $filename): Response
    {
        $path = $this->getParameter('kernel.project_dir') . '/public/' . $filename;

        if (!is_file($path)) {
            return new Response(
                '<?xml version="1.0" encoding="UTF-8"?><error>not_found</error>',
                Response::HTTP_NOT_FOUND,
                ['Content-Type' => 'application/xml; charset=UTF-8']
            );
        }

        return new Response(
            (string) file_get_contents($path),
            Response::HTTP_OK,
            ['Content-Type' => 'application/xml; charset=UTF-8']
        );
    }
}

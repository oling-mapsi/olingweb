<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Twig\Environment;

/** One public trail, based on the rendered heading; no CMS or SEO metadata writes. */
final class PublicBreadcrumbSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly Environment $twig) {}

    public static function getSubscribedEvents(): array
    {
        return ['kernel.response' => ['render', -20]];
    }

    public function render(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        $path = rtrim($request->getPathInfo(), '/') ?: '/';
        $route = (string) $request->attributes->get('_route');
        if (!$event->isMainRequest() || $response->getStatusCode() !== 200
            || !str_contains((string) $response->headers->get('Content-Type'), 'text/html')
            || in_array($path, ['/', '/en', '/es', '/login', '/logout'], true)
            || preg_match('~^/(?:admin|__|preview)(?:/|$)~', $path)
            || str_starts_with($route, 'admin_') || str_contains($route, 'preview')) {
            return;
        }
        $html = (string) $response->getContent();
        if (str_contains($html, 'class="oling-public-breadcrumbs"')
            || !preg_match('~<main\b[^>]*>~i', $html)
            || !preg_match('~<h1\b[^>]*>(.*?)</h1>~is', $html, $heading)) {
            return;
        }
        $title = trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags(preg_replace('~<br\s*/?>~i', ' ', $heading[1])), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        if ($title === '') { return; }
        $locale = in_array($request->getLocale(), ['en', 'es'], true) ? $request->getLocale() : 'fr';
        $labels = [
            'fr' => ['home' => 'Accueil', 'expertise' => 'Expertises', 'resources' => 'Ressources', 'sectors' => 'Secteurs', 'about' => 'Qui sommes-nous ?', 'projects' => 'Projets', 'aria' => 'Fil d’Ariane'],
            'en' => ['home' => 'Home', 'expertise' => 'Expertise', 'resources' => 'Resources', 'sectors' => 'Industries', 'about' => 'About us', 'projects' => 'Projects', 'aria' => 'Breadcrumb'],
            'es' => ['home' => 'Inicio', 'expertise' => 'Especialidades', 'resources' => 'Recursos', 'sectors' => 'Sectores', 'about' => 'Quiénes somos', 'projects' => 'Proyectos', 'aria' => 'Ruta de navegación'],
        ][$locale];
        $parents = [
            'fr' => ['expertise' => '/expertises', 'resources' => '/ressources', 'sectors' => '/secteurs', 'about' => '/a-propos', 'projects' => '/projets'],
            'en' => ['expertise' => '/en/areas-of-expertise', 'resources' => '/en/resources', 'sectors' => '/en/industries', 'about' => '/en/about', 'projects' => '/en/projects'],
            'es' => ['expertise' => '/es/areas-de-especializacion', 'resources' => '/es/recursos', 'sectors' => '/es/sectores', 'about' => '/es/quienes-somos', 'projects' => '/es/proyectos'],
        ][$locale];
        $parent = null;
        foreach (['resources', 'sectors', 'about', 'projects', 'expertise'] as $key) {
            if (str_starts_with($path, $parents[$key].'/')) { $parent = $key; break; }
        }
        $expertisePages = ['amoa-si','erp-progiciel','crm','gmao','rgpd','cyber-securite','conseil-qualite','conformite-reglementaire','si-finance','facturation-electronique-amoa','infrastructure-si-amoa','mapsi-progiciel','gestion-risques-audit-controle-interne','direction-qualite-deleguee','direction-conformite-externalisee','dsi-externalisee','pca-pra-continuite-activite'];
        if ($parent === null && (in_array(ltrim($path, '/'), $expertisePages, true)
            || in_array($route, ['service', 'practice', 'practice_home', 'practice_home_consulting', 'practice_home_business_apps', 'practice_home_expertises_audit'], true)
            || str_starts_with($route, 'localized_service_'))) {
            $parent = 'expertise';
        }
        if ($parent === null && in_array($path, ['/en/team', '/es/equipo'], true)) { $parent = 'about'; }
        $items = [['label' => $labels['home'], 'href' => $locale === 'fr' ? '/' : '/'.$locale]];
        if ($parent !== null && $parents[$parent] !== $path) {
            $items[] = ['label' => $labels[$parent], 'href' => $parents[$parent]];
        }
        $items[] = ['label' => $title, 'href' => null];
        $trail = $this->twig->render('public/_breadcrumbs.html.twig', ['items' => $items, 'breadcrumbLabel' => $labels['aria']]);
        // Remove only the legacy navigation variants; leave BreadcrumbList JSON-LD intact.
        $html = preg_replace('~<nav\b[^>]*aria-label=["\'](?:breadcrumb|fil d[’\x27]ariane)["\'][^>]*>.*?</nav>\s*~isu', '', $html);
        $html = preg_replace_callback('~<main\b[^>]*>~i', static fn ($match) => $trail.$match[0], $html, 1);
        $response->setContent($html);
    }
}

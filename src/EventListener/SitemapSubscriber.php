<?php

namespace App\EventListener;

use App\Repository\PracticeRepository;
use App\Repository\SitePageRepository;
use App\Repository\SitePageTranslationRepository;
use App\Repository\ServicesRepository;
use App\Service\PublicSitePageResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\UrlContainerInterface;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;

class SitemapSubscriber implements EventSubscriberInterface
{
    private PracticeRepository $practiceRepository;
    private ServicesRepository $servicesRepository;
    private SitePageRepository $sitePageRepository;
    private SitePageTranslationRepository $sitePageTranslationRepository;
    private PublicSitePageResolver $publicSitePageResolver;

    public function __construct(
        PracticeRepository $practiceRepository,
        ServicesRepository $servicesRepository,
        SitePageRepository $sitePageRepository,
        SitePageTranslationRepository $sitePageTranslationRepository,
        PublicSitePageResolver $publicSitePageResolver
    ) {
        $this->practiceRepository = $practiceRepository;
        $this->servicesRepository = $servicesRepository;
        $this->sitePageRepository = $sitePageRepository;
        $this->sitePageTranslationRepository = $sitePageTranslationRepository;
        $this->publicSitePageResolver = $publicSitePageResolver;
    }

    public static function getSubscribedEvents()
    {
        return [
            SitemapPopulateEvent::class => 'populate',
        ];
    }

    public function populate(SitemapPopulateEvent $event): void
    {
        $this->registerPracticeUrls($event->getUrlContainer(), $event->getUrlGenerator());
        $this->registerServicesUrls($event->getUrlContainer(), $event->getUrlGenerator());
        $this->registerPublicSiteUrls($event->getUrlContainer(), $event->getUrlGenerator());
        $this->registerSeoResourceUrls($event->getUrlContainer(), $event->getUrlGenerator());
        $this->registerLocalizedSitePageUrls($event->getUrlContainer(), $event->getUrlGenerator());
    }

    public function registerPracticeUrls(UrlContainerInterface $urls, UrlGeneratorInterface $router): void
    {
        $practices = $this->practiceRepository->findAll();

        foreach ($practices as $practice) {
            $slug = $practice->getSlug();
            if (!$slug || $slug === 'amoa-si') {
                continue;
            }

            $urls->addUrl(
                new UrlConcrete(
                    $router->generate(
                        'practice_home',
                        [
                            'slug' => $slug,
                        ],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    )
                ),
                'practice'
            );
        }
    }

    public function registerServicesUrls(UrlContainerInterface $urls, UrlGeneratorInterface $router): void
    {
        $services = $this->servicesRepository->findAll();

        foreach ($services as $service) {
            $practice = $service->getPractice();
            if (!$practice || !$practice->getSlug()) {
                continue;
            }

            $urls->addUrl(
                new UrlConcrete(
                    $router->generate(
                        'service',
                        [
                            'practice' => $practice->getSlug(),
                            'slug' => $service->getSlug(),
                        ],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    )
                ),
                'services'
            );
        }
    }

    public function registerSeoResourceUrls(UrlContainerInterface $urls, UrlGeneratorInterface $router): void
    {
        foreach ($this->sitePageRepository->findResourceArticles() as $article) {
            $storedSlug = (string) $article->getSlug();
            if (!str_starts_with($storedSlug, 'ressource-')) {
                continue;
            }

            $publicSlug = substr($storedSlug, strlen('ressource-'));
            if ($publicSlug === false || $publicSlug === '') {
                continue;
            }

            $urls->addUrl(
                new UrlConcrete(
                    $router->generate(
                        'seo_resource',
                        ['slug' => $publicSlug],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    )
                ),
                'default'
            );
        }
    }

    public function registerPublicSiteUrls(UrlContainerInterface $urls, UrlGeneratorInterface $router): void
    {
        foreach (array_keys($this->publicSitePageResolver->getExpertisePages()) as $slug) {
            $urls->addUrl(
                new UrlConcrete(
                    $router->generate(
                        'expertises_show',
                        ['slug' => $slug],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    )
                ),
                'default'
            );
        }

        foreach (array_keys($this->publicSitePageResolver->getSectorPages()) as $slug) {
            $urls->addUrl(
                new UrlConcrete(
                    $router->generate(
                        'sectors_show',
                        ['slug' => $slug],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    )
                ),
                'default'
            );
        }
    }

    public function registerLocalizedSitePageUrls(UrlContainerInterface $urls, UrlGeneratorInterface $router): void
    {
        foreach (['en', 'es'] as $locale) {
            foreach ($this->sitePageTranslationRepository->findPublishedByLocale($locale) as $translation) {
                $page = $translation->getSitePage();
                $route = $page?->getSlug() === 'home' ? 'localized_homepage' : 'localized_site_page_show';
                $parameters = $route === 'localized_homepage'
                    ? ['_locale' => $locale]
                    : ['_locale' => $locale, 'slug' => $translation->getSlug()];

                $urls->addUrl(
                    new UrlConcrete(
                        $router->generate(
                            $route,
                            $parameters,
                            UrlGeneratorInterface::ABSOLUTE_URL
                        )
                    ),
                    $locale
                );
            }
        }
    }
}

<?php

namespace App\EventListener;

use App\Controller\SeoResourceController;
use App\Repository\PracticeRepository;
use App\Repository\ServicesRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\UrlContainerInterface;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;

class SitemapSubscriber implements EventSubscriberInterface
{
    private PracticeRepository $practiceRepository;
    private ServicesRepository $servicesRepository;

    public function __construct(
        PracticeRepository $practiceRepository,
        ServicesRepository $servicesRepository
    ) {
        $this->practiceRepository = $practiceRepository;
        $this->servicesRepository = $servicesRepository;
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
        $this->registerSeoResourceUrls($event->getUrlContainer(), $event->getUrlGenerator());
    }

    public function registerPracticeUrls(UrlContainerInterface $urls, UrlGeneratorInterface $router): void
    {
        $practices = $this->practiceRepository->findAll();

        foreach ($practices as $practice) {
            $slug = $practice->getSlug();
            if (!$slug || in_array($slug, ['consulting', 'amoa-si'], true)) {
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
        foreach (SeoResourceController::articles() as $article) {
            $urls->addUrl(
                new UrlConcrete(
                    $router->generate(
                        'seo_resource',
                        ['slug' => $article['slug']],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    )
                ),
                'default'
            );
        }
    }
}

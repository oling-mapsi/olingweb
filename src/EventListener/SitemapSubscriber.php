<?php

namespace App\EventListener;

use App\Repository\PracticeRepository;
use App\Repository\ServicesRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\UrlContainerInterface;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use App\Middleware\XRobotsTagMiddleware;


class SitemapSubscriber implements EventSubscriberInterface
{
    private $practiceRepository;
    private $servicesRepository;
    private $urlGenerator;

    public function __construct(
        PracticeRepository $practiceRepository,
        ServicesRepository $servicesRepository,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->practiceRepository = $practiceRepository;
        $this->servicesRepository = $servicesRepository;
        $this->urlGenerator = $urlGenerator;
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
    }

    public function registerPracticeUrls(UrlContainerInterface $urls, UrlGeneratorInterface $router): void
    {
        $practices = $this->practiceRepository->findAll();

        foreach ($practices as $practice) {
            $urls->addUrl(
                new UrlConcrete(
                    $router->generate(
                        'service',
                        [
                            'practice' => $practice->getId(),
                            'id' => $practice->getId(),
                            'slug' => $practice->getSlug(),
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
            $urls->addUrl(
                new UrlConcrete(
                    $router->generate(
                        'service',
                        [
                            'practice' => $service->getPractice()->getSlug(),
                            'id' => $service->getId(),
                            'slug' => $service->getSlug(),
                        ],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    )
                ),
                'services'
            );
        }
    }
}

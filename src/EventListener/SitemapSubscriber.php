<?php

namespace App\EventListener;

use App\Repository\PracticeRepository;
use App\Repository\ServicesRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\UrlContainerInterface;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;

class SitemapSubscriber implements EventSubscriberInterface
{
    /**
     * @var PracticeRepository
     */
    private $practiceRepository;

    /**
     * @var ServicesRepository
     */
    private $servicesRepository;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @param PracticeRepository $practiceRepository
     * @param ServicesRepository $servicesRepository
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(
        PracticeRepository $practiceRepository,
        ServicesRepository $servicesRepository,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->practiceRepository = $practiceRepository;
        $this->servicesRepository = $servicesRepository;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            SitemapPopulateEvent::class => 'populate',
        ];
    }

    /**
     * @param SitemapPopulateEvent $event
     */
    public function populate(SitemapPopulateEvent $event): void
    {
        $this->registerPracticeUrls($event->getUrlContainer(), $event->getUrlGenerator());
        $this->registerServicesUrls($event->getUrlContainer(), $event->getUrlGenerator());
    }

    /**
     * @param UrlContainerInterface $urls
     * @param UrlGeneratorInterface $router
     */
    public function registerPracticeUrls(UrlContainerInterface $urls, UrlGeneratorInterface $router): void
    {
        $practices = $this->practiceRepository->findAll();

        foreach ($practices as $practice) {
            $urls->addUrl(
                new UrlConcrete(
                    $router->generate(
                        'practice',
                        [
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

    /**
     * @param UrlContainerInterface $urls
     * @param UrlGeneratorInterface $router
     */
    public function registerServicesUrls(UrlContainerInterface $urls, UrlGeneratorInterface $router): void
    {
        $services = $this->servicesRepository->findAll();

        foreach ($services as $service) {
            // Vérifier que la colonne IntroductionShort n'est pas vide
            if (empty($service->getIntroductionShort())) {
                continue;
            }

            $urls->addUrl(
                new UrlConcrete(
                    $router->generate(
                        'service',
                        [
                            'practice' => $service->getPractice()->getId(),
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

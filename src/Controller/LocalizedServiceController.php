<?php

namespace App\Controller;

use App\Entity\SitePageTranslation;
use App\Repository\PracticeRepository;
use App\Repository\ServicesRepository;
use App\Service\LocalizedContentResolver;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class LocalizedServiceController extends AbstractController
{
    public function __construct(
        private readonly Connection $connection,
        private readonly LocalizedContentResolver $localizedContentResolver,
        private readonly PracticeRepository $practiceRepository,
        private readonly ServicesRepository $servicesRepository,
    ) {
    }

    #[Route('/en/services/{slug}', name: 'localized_service_en', defaults: ['_locale' => SitePageTranslation::LOCALE_EN], requirements: ['slug' => '[a-z0-9][a-z0-9\-]*'], methods: ['GET'], priority: 90)]
    #[Route('/es/servicios/{slug}', name: 'localized_service_es', defaults: ['_locale' => SitePageTranslation::LOCALE_ES], requirements: ['slug' => '[a-z0-9][a-z0-9\-]*'], methods: ['GET'], priority: 90)]
    public function show(Request $request, string $_locale, string $slug): Response
    {
        SitePageTranslation::assertSupportedLocale($_locale);
        $request->setLocale($_locale);

        $serviceId = $this->connection->fetchOne(
            'SELECT service_id FROM service_translation WHERE locale = :locale AND slug = :slug AND translation_status = :status',
            ['locale' => $_locale, 'slug' => $slug, 'status' => 'published']
        );

        if ($serviceId === false) {
            throw $this->createNotFoundException('Localized service not found.');
        }

        $service = $this->servicesRepository->find((int) $serviceId);
        if ($service === null || $service->getPractice() === null) {
            throw $this->createNotFoundException('Localized service not found.');
        }

        $serviceContent = $this->localizedContentResolver->getServiceView($service, $_locale);

        return $this->render('services.html.twig', [
            'controller_name' => 'LocalizedServiceController',
            'service' => $serviceContent,
            'serviceNarrative' => $serviceContent->getPublicNarrative(),
            'pract' => $serviceContent->getPractice()?->getSlug(),
            'practices' => $this->localizePractices($_locale),
            'services' => $this->localizeServices($_locale),
            'canonicalPath' => $this->localizedServicePath($_locale, $slug),
            'localizedAlternates' => $this->localizedServiceAlternates((int) $serviceId, $service->getPractice()->getSlug(), $service->getSlug()),
        ]);
    }

    private function localizePractices(string $locale): array
    {
        return array_map(fn ($practice) => $this->localizedContentResolver->getPracticeView($practice, $locale), $this->practiceRepository->findAll());
    }

    private function localizeServices(string $locale): array
    {
        return array_map(fn ($service) => $this->localizedContentResolver->getServiceView($service, $locale), $this->servicesRepository->findAll());
    }

    /**
     * @return array<string, string>
     */
    private function localizedServiceAlternates(int $serviceId, string $frenchPracticeSlug, string $frenchServiceSlug): array
    {
        $alternates = [
            SitePageTranslation::LOCALE_FR => '/'.$frenchPracticeSlug.'/'.$frenchServiceSlug,
            'x-default' => '/'.$frenchPracticeSlug.'/'.$frenchServiceSlug,
        ];

        $rows = $this->connection->fetchAllAssociative(
            'SELECT locale, slug FROM service_translation WHERE service_id = :id AND locale IN (:locales) AND translation_status = :status AND slug IS NOT NULL AND slug <> :empty',
            [
                'id' => $serviceId,
                'locales' => [SitePageTranslation::LOCALE_EN, SitePageTranslation::LOCALE_ES],
                'status' => 'published',
                'empty' => '',
            ],
            ['locales' => Connection::PARAM_STR_ARRAY]
        );

        foreach ($rows as $row) {
            $alternates[(string) $row['locale']] = $this->localizedServicePath((string) $row['locale'], (string) $row['slug']);
        }

        return $alternates;
    }

    private function localizedServicePath(string $locale, string $slug): string
    {
        return match ($locale) {
            SitePageTranslation::LOCALE_EN => '/en/services/'.$slug,
            SitePageTranslation::LOCALE_ES => '/es/servicios/'.$slug,
            default => '/'.$slug,
        };
    }
}

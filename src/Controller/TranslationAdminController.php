<?php

namespace App\Controller;

use App\Entity\SitePageTranslation;
use App\Repository\SitePageRepository;
use App\Repository\SitePageTranslationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/translations')]
final class TranslationAdminController extends AbstractController
{
    #[Route('', name: 'admin_translations_index', methods: ['GET'])]
    #[Route('/', name: 'admin_translations_index_slash', methods: ['GET'])]
    public function index(SitePageRepository $pages, Request $request): Response
    {
        $family = (string) $request->query->get('family', 'expertises-secteurs');
        $locale = (string) $request->query->get('locale', 'all');
        $status = (string) $request->query->get('status', 'all');

        $rows = [];
        foreach ($pages->findBy([], ['slug' => 'ASC']) as $page) {
            $slug = (string) $page->getSlug();
            if ($family === 'expertises-secteurs' && !$this->isExpertiseOrSector($slug)) {
                continue;
            }

            foreach ([SitePageTranslation::LOCALE_EN, SitePageTranslation::LOCALE_ES] as $targetLocale) {
                if ($locale !== 'all' && $locale !== $targetLocale) {
                    continue;
                }
                $translation = $page->getTranslation($targetLocale);
                $translationStatus = $translation?->getTranslationStatus() ?? 'missing';
                if ($status !== 'all' && $status !== $translationStatus) {
                    continue;
                }
                $rows[] = [
                    'page' => $page,
                    'source' => $page->getTranslation(SitePageTranslation::LOCALE_FR),
                    'translation' => $translation,
                    'locale' => $targetLocale,
                    'status' => $translationStatus,
                ];
            }
        }

        return $this->render('admin/translations/index.html.twig', [
            'rows' => $rows,
            'family' => $family,
            'locale' => $locale,
            'status' => $status,
            'practices' => [],
        ]);
    }

    #[Route('/site-page/{id}/{locale}', name: 'admin_translations_site_page_edit', methods: ['GET', 'POST'])]
    public function editSitePage(
        int $id,
        string $locale,
        Request $request,
        SitePageTranslationRepository $translations,
        EntityManagerInterface $entityManager
    ): Response {
        SitePageTranslation::assertSupportedLocale($locale);
        $target = $translations->find($id);
        if (!$target instanceof SitePageTranslation || $target->getLocale() !== $locale) {
            throw $this->createNotFoundException('Traduction introuvable.');
        }

        $source = $target->getSitePage()?->getTranslation(SitePageTranslation::LOCALE_FR);
        if (!$source instanceof SitePageTranslation) {
            throw $this->createNotFoundException('Source FR introuvable.');
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('translation_edit_'.$target->getId(), (string) $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Token CSRF invalide.');
            }

            $action = (string) $request->request->get('action', 'save');
            if ($action === 'publish') {
                $target->setTranslationStatus(SitePageTranslation::STATUS_PUBLISHED);
                $target->setPublishedAt(new \DateTimeImmutable());
                $target->setUnpublishedAt(null);
            } elseif ($action === 'review') {
                $target->setTranslationStatus(SitePageTranslation::STATUS_REVIEWED);
                $target->setReviewedAt(new \DateTimeImmutable());
            } else {
                $target
                    ->setTitle((string) $request->request->get('title'))
                    ->setSlug((string) $request->request->get('slug'))
                    ->setHeroBadge($this->nullableString($request->request->get('heroBadge')))
                    ->setHeroTitle($this->nullableString($request->request->get('heroTitle')))
                    ->setHeroIntro($this->nullableString($request->request->get('heroIntro')))
                    ->setSeoTitle($this->nullableString($request->request->get('seoTitle')))
                    ->setSeoDescription($this->nullableString($request->request->get('seoDescription')))
                    ->setBodyHtml($this->nullableString($request->request->get('bodyHtml')));
            }

            $entityManager->flush();
            $this->addFlash('success', 'Traduction mise à jour.');

            return $this->redirectToRoute('admin_translations_site_page_edit', ['id' => $target->getId(), 'locale' => $locale]);
        }

        return $this->render('admin/translations/site_page_edit.html.twig', [
            'source' => $source,
            'target' => $target,
            'practices' => [],
        ]);
    }

    private function isExpertiseOrSector(string $slug): bool
    {
        return $slug === 'expertises-index'
            || $slug === 'secteurs-index'
            || str_starts_with($slug, 'expertise-')
            || str_starts_with($slug, 'secteur-');
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}

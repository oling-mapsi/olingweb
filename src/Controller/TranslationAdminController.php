<?php

namespace App\Controller;

use App\Entity\SitePageTranslation;
use App\Entity\User;
use App\Repository\SitePageRepository;
use App\Repository\SitePageTranslationRepository;
use App\Service\I18n\AiTranslationService;
use App\Service\I18n\AiTranslationValidationException;
use App\Service\I18n\TranslationReviewWorkflow;
use App\Service\TranslationSourceHasher;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/translations')]
final class TranslationAdminController extends AbstractController
{
    #[Route('', name: 'admin_translations_index', methods: ['GET'])]
    #[Route('/', name: 'admin_translations_index_slash', methods: ['GET'])]
    public function index(SitePageRepository $pages, Request $request, TranslationSourceHasher $sourceHasher): Response
    {
        $type = (string) $request->query->get('type', 'all');
        $locale = (string) $request->query->get('locale', 'all');
        $status = (string) $request->query->get('status', 'all');

        $rows = [];
        foreach ($pages->findBy([], ['slug' => 'ASC']) as $page) {
            $slug = (string) $page->getSlug();
            $pageType = $this->pageType($slug);
            if ($type !== 'all' && $type !== $pageType) {
                continue;
            }
            $source = $page->getTranslation(SitePageTranslation::LOCALE_FR);
            $sourceHash = $source instanceof SitePageTranslation ? $sourceHasher->hashSitePageTranslation($source) : null;

            foreach ([SitePageTranslation::LOCALE_EN, SitePageTranslation::LOCALE_ES] as $targetLocale) {
                if ($locale !== 'all' && $locale !== $targetLocale) {
                    continue;
                }
                $translation = $page->getTranslation($targetLocale);
                $translationStatus = $translation?->getTranslationStatus() ?? 'missing';
                $outdated = $translation instanceof SitePageTranslation && $sourceHash !== null && $translation->isOutdated($sourceHash);
                if ($status !== 'all' && $status !== $translationStatus) {
                    if ($status !== 'outdated' || !$outdated) {
                        continue;
                    }
                }
                if ($status === 'outdated' && !$outdated) {
                    continue;
                }
                $rows[] = [
                    'page' => $page,
                    'source' => $source,
                    'translation' => $translation,
                    'locale' => $targetLocale,
                    'status' => $translationStatus,
                    'outdated' => $outdated,
                    'type' => $pageType,
                ];
            }
        }

        return $this->render('admin/translations/index.html.twig', [
            'rows' => $rows,
            'type' => $type,
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
        EntityManagerInterface $entityManager,
        TranslationSourceHasher $sourceHasher
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

            $sourceHash = $sourceHasher->hashSitePageTranslation($source);
            $action = (string) $request->request->get('action', 'save');
            $workflow = new TranslationReviewWorkflow();

            try {
                if ($action === 'publish') {
                    $user = $this->getUser();
                    $workflow->publishFromAdmin($target, $sourceHash, $user instanceof User ? $user : null);
                } elseif ($action === 'unpublish') {
                    $workflow->unpublish($target);
                } elseif ($action === 'review') {
                    $user = $this->getUser();
                    $workflow->markReviewed($target, $user instanceof User ? $user : null);
                    $target->setSourceContentHash($sourceHash);
                } elseif ($action === 'to_review') {
                    $workflow->markToReview($target);
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
                $this->addFlash('success', $this->successMessage($action, $target->getLocale()));
            } catch (UniqueConstraintViolationException) {
                $this->addFlash('danger', sprintf('Impossible de publier : le slug "%s" est déjà utilisé pour cette langue.', $target->getSlug()));
            } catch (AiTranslationValidationException $exception) {
                $this->addFlash('danger', 'Impossible de publier : '.$exception->getMessage());
            }

            return $this->redirectToRoute('admin_translations_site_page_edit', ['id' => $target->getId(), 'locale' => $locale]);
        }

        return $this->render('admin/translations/site_page_edit.html.twig', [
            'source' => $source,
            'target' => $target,
            'outdated' => $target->isOutdated($sourceHasher->hashSitePageTranslation($source)),
            'practices' => [],
        ]);
    }

    #[Route('/site-page/{id}/{locale}/preview', name: 'admin_translations_site_page_preview', methods: ['GET'])]
    public function previewSitePage(int $id, string $locale, SitePageTranslationRepository $translations): RedirectResponse
    {
        SitePageTranslation::assertSupportedLocale($locale);
        $translation = $translations->find($id);
        if (!$translation instanceof SitePageTranslation || $translation->getLocale() !== $locale || $translation->getSitePage() === null) {
            throw $this->createNotFoundException('Traduction introuvable.');
        }

        return $this->redirectToRoute('admin_pages_preview', [
            'id' => $translation->getSitePage()->getId(),
            'locale' => $locale,
        ]);
    }

    #[Route('/site-page/{id}/{locale}/generate', name: 'admin_translations_site_page_generate', methods: ['POST'])]
    public function generateSitePage(
        int $id,
        string $locale,
        Request $request,
        SitePageRepository $pages,
        AiTranslationService $translationService,
        EntityManagerInterface $entityManager
    ): Response {
        SitePageTranslation::assertSupportedLocale($locale);
        if (!$this->isCsrfTokenValid('translation_generate_'.$id.'_'.$locale, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $page = $pages->find($id);
        if ($page === null) {
            throw $this->createNotFoundException('Page introuvable.');
        }

        $overwrite = (string) $request->request->get('confirm_overwrite') === '1';
        $existing = $page->getTranslation($locale);
        if ($existing instanceof SitePageTranslation && !$overwrite) {
            $this->addFlash('warning', 'Traduction existante non écrasée : confirmez la régénération.');

            return $this->redirectToRoute('admin_translations_site_page_edit', ['id' => $existing->getId(), 'locale' => $locale]);
        }

        try {
            $translation = $translationService->translateSitePage($page, $locale, $overwrite, false, false);
            $entityManager->flush();
            $this->addFlash('success', 'Traduction IA générée en statut ai_translated, non publiée.');

            return $this->redirectToRoute('admin_translations_site_page_edit', ['id' => $translation->getId(), 'locale' => $locale]);
        } catch (\Throwable $exception) {
            $this->addFlash('danger', 'Génération IA impossible : '.$exception->getMessage());

            return $existing instanceof SitePageTranslation
                ? $this->redirectToRoute('admin_translations_site_page_edit', ['id' => $existing->getId(), 'locale' => $locale])
                : $this->redirectToRoute('admin_translations_index');
        }
    }

    #[Route('/bulk-generate', name: 'admin_translations_bulk_generate', methods: ['POST'])]
    public function bulkGenerate(
        Request $request,
        SitePageRepository $pages,
        AiTranslationService $translationService,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('translation_bulk_generate', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $type = (string) $request->request->get('type', '');
        $locale = (string) $request->request->get('locale', '');
        SitePageTranslation::assertSupportedLocale($locale);
        if ($locale === SitePageTranslation::LOCALE_FR || !in_array($type, ['page', 'expertise', 'sector', 'resource'], true)) {
            throw $this->createAccessDeniedException('Filtres bulk invalides.');
        }

        $expected = max(0, (int) $request->request->get('expected_count'));
        $generated = 0;
        $candidates = [];
        foreach ($pages->findBy([], ['slug' => 'ASC']) as $page) {
            if ($this->pageType((string) $page->getSlug()) !== $type || $page->getTranslation($locale) instanceof SitePageTranslation) {
                continue;
            }
            $candidates[] = $page;
        }
        if ($expected !== count($candidates)) {
            $this->addFlash('danger', sprintf('Bulk annulé : %d contenu(s) attendus, %d trouvé(s).', $expected, count($candidates)));

            return $this->redirectToRoute('admin_translations_index', ['type' => $type, 'locale' => $locale, 'status' => 'missing']);
        }

        foreach ($candidates as $page) {
            $translationService->translateSitePage($page, $locale, false, true, false);
            ++$generated;
        }
        $entityManager->flush();
        $this->addFlash('success', sprintf('%d traduction(s) générée(s) en statut ai_translated, non publiées.', $generated));

        return $this->redirectToRoute('admin_translations_index', ['type' => $type, 'locale' => $locale, 'status' => SitePageTranslation::STATUS_AI_TRANSLATED]);
    }

    private function isExpertiseOrSector(string $slug): bool
    {
        return $slug === 'expertises-index'
            || $slug === 'secteurs-index'
            || str_starts_with($slug, 'expertise-')
            || str_starts_with($slug, 'secteur-');
    }

    private function pageType(string $slug): string
    {
        if ($slug === 'expertises-index' || str_starts_with($slug, 'expertise-')) {
            return 'expertise';
        }
        if ($slug === 'secteurs-index' || str_starts_with($slug, 'secteur-')) {
            return 'sector';
        }
        if (str_starts_with($slug, 'ressource-')) {
            return 'resource';
        }

        return 'page';
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function successMessage(string $action, string $locale): string
    {
        return match ($action) {
            'publish' => sprintf('Traduction %s publiée.', strtoupper($locale)),
            'unpublish' => sprintf('Traduction %s dépubliée.', strtoupper($locale)),
            'review' => sprintf('Traduction %s marquée comme relue.', strtoupper($locale)),
            'to_review' => sprintf('Traduction %s passée à relire.', strtoupper($locale)),
            default => 'Traduction mise à jour.',
        };
    }
}

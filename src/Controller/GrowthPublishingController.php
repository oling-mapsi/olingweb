<?php

namespace App\Controller;

use App\Dto\GrowthNewsInput;
use App\Service\GrowthApiAuthenticator;
use App\Service\GrowthPreviewSigner;
use App\Service\GrowthPublishingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/growth/news')]
class GrowthPublishingController extends AbstractController
{
    #[Route('/media', name: 'growth_api_media_import', methods: ['POST'], priority: 100)]
    public function importMedia(Request $request, \App\Service\GrowthMediaStore $media): JsonResponse
    {
        $this->authenticator->assertAuthorized($request);
        $result = $media->import($request->toArray());
        $result['public_url'] = $request->getSchemeAndHttpHost().$result['public_path'];
        return $this->json($result);
    }

    public function __construct(
        private readonly GrowthApiAuthenticator $authenticator,
        private readonly GrowthPublishingService $publishingService,
        private readonly GrowthPreviewSigner $previewSigner,
        private readonly ValidatorInterface $validator
    ) {
    }

    #[Route('', name: 'growth_api_news_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->authenticator->assertAuthorized($request);
        $input = $this->validatePayload($request);
        $payload = $this->publishingService->createOrUpdateDraft($input);

        return $this->json($payload, $payload['http_code']);
    }

    #[Route('/{externalId}', name: 'growth_api_news_update', methods: ['PATCH'])]
    public function update(string $externalId, Request $request): JsonResponse
    {
        $this->authenticator->assertAuthorized($request);
        $input = $this->validatePayload($request);
        if ($input->externalId !== $externalId) {
            throw new BadRequestHttpException('external_id mismatch between URL and payload.');
        }

        $payload = $this->publishingService->createOrUpdateDraft($input);

        return $this->json($payload, $payload['http_code']);
    }

    #[Route('/{externalId}', name: 'growth_api_news_status', methods: ['GET'])]
    public function status(string $externalId, Request $request): JsonResponse
    {
        $this->authenticator->assertAuthorized($request);

        return $this->json($this->publishingService->getStatus($externalId, (string) $request->query->get('locale', 'fr')));
    }

    #[Route('/{externalId}/preview-url', name: 'growth_api_news_preview_url', methods: ['POST'])]
    public function previewUrl(string $externalId, Request $request): JsonResponse
    {
        $this->authenticator->assertAuthorized($request);
        $locale = (string) $request->query->get('locale', 'fr');
        $revision = $this->publishingService->buildPreviewPage($externalId, $locale);
        $signature = $this->previewSigner->generateParameters($externalId, (int) $revision->getId());

        return $this->json([
            'external_id' => $externalId,
            'preview_url' => $this->generateUrl('growth_preview_article', [
                'externalId' => $externalId,
                'revisionId' => $revision->getId(),
                'expires' => $signature['expires'],
                'signature' => $signature['signature'],
                'locale' => $locale,
            ], UrlGeneratorInterface::ABSOLUTE_URL),
            'expires_at' => gmdate(DATE_ATOM, $signature['expires']),
        ]);
    }

    #[Route('/{externalId}/publish', name: 'growth_api_news_publish', methods: ['POST'])]
    public function publish(string $externalId, Request $request): JsonResponse
    {
        $this->authenticator->assertAuthorized($request);

        return $this->json($this->publishingService->publish($externalId, (string) $request->query->get('locale', 'fr')), Response::HTTP_OK);
    }

    #[Route('/{externalId}/unpublish', name: 'growth_api_news_unpublish', methods: ['POST'])]
    public function unpublish(string $externalId, Request $request): JsonResponse
    {
        $this->authenticator->assertAuthorized($request);

        return $this->json($this->publishingService->unpublish($externalId, (string) $request->query->get('locale', 'fr')), Response::HTTP_OK);
    }

    #[Route('/{externalId}/restore-previous', name: 'growth_api_news_restore_previous', methods: ['POST'])]
    public function restorePrevious(string $externalId, Request $request): JsonResponse
    {
        $this->authenticator->assertAuthorized($request);

        return $this->json($this->publishingService->restorePreviousPublishedVersion($externalId, (string) $request->query->get('locale', 'fr')), Response::HTTP_OK);
    }

    private function validatePayload(Request $request): GrowthNewsInput
    {
        try {
            $payload = $request->toArray();
        } catch (\Throwable) {
            throw new BadRequestHttpException('Invalid JSON payload.');
        }

        $input = GrowthNewsInput::fromArray($payload);
        $violations = $this->validator->validate($input);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()][] = $violation->getMessage();
            }

            throw new BadRequestHttpException(json_encode(['errors' => $errors], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: 'Validation failed.');
        }

        return $input;
    }
}

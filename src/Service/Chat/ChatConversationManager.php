<?php

namespace App\Service\Chat;

use App\Entity\ChatConversation;
use App\Entity\ChatLead;
use App\Entity\ChatMessage;
use App\Repository\ChatConversationRepository;
use Doctrine\ORM\EntityManagerInterface;

class ChatConversationManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ChatConversationRepository $conversationRepository,
        private readonly ChatResponder $chatResponder,
        private readonly ChatQualificationService $qualificationService,
        private readonly ChatSummaryService $summaryService,
        private readonly ChatLeadMailer $leadMailer,
        private readonly PublicContentCatalog $publicContentCatalog,
        private readonly AiConsultantContentProvider $contentProvider,
    ) {
    }

    public function createConversation(?string $sourcePath, ?string $sourceUrl, ?string $referrer, ?string $locale, ?string $ip, ?string $userAgent): ChatConversation
    {
        $locale = in_array($locale, ['fr', 'en', 'es'], true) ? $locale : AiConsultantContentProvider::LOCALE;
        $conversation = (new ChatConversation())
            ->setPublicToken(bin2hex(random_bytes(24)))
            ->setStatus(ChatConversation::STATUS_ACTIVE)
            ->setSourcePath($sourcePath)
            ->setSourceUrl($sourceUrl)
            ->setReferrer($referrer)
            ->setLocale($locale)
            ->setPromptVersion(AiConsultantContentProvider::VERSION)
            ->setIpHash($ip ? hash('sha256', $ip) : null)
            ->setUserAgentHash($userAgent ? hash('sha256', $userAgent) : null);

        $this->entityManager->persist($conversation);
        $this->addAssistantMessage($conversation, $this->chatResponder->getWelcomeMessage($conversation->getLocale() ?: AiConsultantContentProvider::LOCALE), 'welcome');
        $this->entityManager->flush();

        return $conversation;
    }

    public function findByPublicToken(string $token): ?ChatConversation
    {
        return $this->conversationRepository->findOneByPublicToken($token);
    }

    public function handleVisitorMessage(ChatConversation $conversation, string $content, ?string $sourcePath, ?string $sourceUrl): ChatReply
    {
        $conversation
            ->setSourcePath($sourcePath ?: $conversation->getSourcePath())
            ->setSourceUrl($sourceUrl ?: $conversation->getSourceUrl());

        $this->addVisitorMessage($conversation, $content);
        $reply = $this->chatResponder->reply($conversation, $content);
        $qualification = $reply->qualification !== [] ? $reply->qualification : $this->qualificationService->qualify($conversation);
        $this->addAssistantMessage($conversation, $reply->content, $reply->messageType, $reply->sources, $reply);

        $conversation->setQualification($qualification);
        $conversation->setStatus($reply->requestLead ? ChatConversation::STATUS_LEAD_PENDING : ChatConversation::STATUS_ACTIVE);
        $this->touchConversation($conversation);
        $this->entityManager->flush();

        return $reply;
    }

    public function handleTechnicalFailure(ChatConversation $conversation, string $content, ?string $sourcePath, ?string $sourceUrl, ?string $errorCode = null): ChatReply
    {
        $conversation
            ->setSourcePath($sourcePath ?: $conversation->getSourcePath())
            ->setSourceUrl($sourceUrl ?: $conversation->getSourceUrl());

        $lastMessage = $conversation->getMessages()->last();
        if (!$lastMessage instanceof ChatMessage || $lastMessage->getRole() !== 'visitor' || trim($lastMessage->getContent()) !== trim($content)) {
            $this->addVisitorMessage($conversation, $content);
        }

        $qualification = $conversation->getQualification() ?: $this->qualificationService->qualify($conversation);
        $reply = new ChatReply(
            $this->contentProvider->text('copy.assistant_unavailable', $conversation->getLocale() ?: AiConsultantContentProvider::LOCALE),
            false,
            [],
            $qualification,
            'technical_error',
            'technical_unavailable',
            null,
            true,
            null,
            [],
            null,
            null,
            null,
            $errorCode,
            null,
            'technical_error'
        );

        $this->addAssistantMessage($conversation, $reply->content, $reply->messageType, [], $reply);
        $conversation->setQualification($qualification);
        $conversation->setStatus(ChatConversation::STATUS_ACTIVE);
        $this->touchConversation($conversation);
        $this->entityManager->flush();

        return $reply;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, string|null>
     */
    public function submitLead(ChatConversation $conversation, array $payload): array
    {
        $fullName = trim((string) ($payload['fullName'] ?? ''));
        $email = trim((string) ($payload['email'] ?? ''));
        $phone = trim((string) ($payload['phone'] ?? ''));
        $company = trim((string) ($payload['company'] ?? ''));
        $needDescription = trim((string) ($payload['needDescription'] ?? ''));
        $rgpdConsent = (bool) ($payload['rgpdConsent'] ?? false);

        if ($fullName === '' || $email === '' || $company === '' || $needDescription === '') {
            throw new \InvalidArgumentException($this->contentProvider->text('copy.lead_required', $conversation->getLocale() ?: AiConsultantContentProvider::LOCALE));
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException($this->contentProvider->text('copy.lead_invalid_email', $conversation->getLocale() ?: AiConsultantContentProvider::LOCALE));
        }

        if (!$rgpdConsent) {
            throw new \InvalidArgumentException($this->contentProvider->text('copy.lead_consent_required', $conversation->getLocale() ?: AiConsultantContentProvider::LOCALE));
        }

        $now = new \DateTimeImmutable();
        $lead = $conversation->getLead() ?? new ChatLead();
        $lead
            ->setConversation($conversation)
            ->setFullName($fullName)
            ->setEmail($email)
            ->setPhone($phone)
            ->setCompany($company)
            ->setNeedDescription($needDescription)
            ->setRgpdConsent(true)
            ->setRgpdConsentAt($now)
            ->setCreatedAt($lead->getCreatedAt() ?? $now);

        $conversation
            ->setConsentAt($now)
            ->setLead($lead)
            ->setSubmittedAt($now)
            ->setStatus(ChatConversation::STATUS_SUBMITTED)
            ->setRetentionPurgeAt($now->modify('+180 days'))
            ->setExpiresAt($now->modify('+180 days'));

        $qualification = $this->qualificationService->qualify($conversation);
        $conversation->setQualification($qualification);

        $summary = $this->summaryService->build($conversation, $lead, $qualification);
        $conversation
            ->setSummaryShort($summary['short'])
            ->setSummaryLong($summary['long']);

        $this->entityManager->persist($lead);
        $this->leadMailer->send($conversation, $lead, $qualification);
        $conversation->setEmailSentAt($now);
        $this->addAssistantMessage(
            $conversation,
            $this->contentProvider->text('copy.lead_confirmation', $conversation->getLocale() ?: AiConsultantContentProvider::LOCALE),
            'confirmation'
        );
        $this->touchConversation($conversation);
        $this->entityManager->flush();

        return $qualification;
    }

    public function serializeConversation(ChatConversation $conversation): array
    {
        return [
            'token' => $conversation->getPublicToken(),
            'status' => $conversation->getStatus(),
            'summaryShort' => $conversation->getSummaryShort(),
            'messages' => array_map(
                fn (ChatMessage $message): array => [
                    'role' => $message->getRole(),
                    'content' => $this->normalizeSerializedMessageContent($message),
                    'type' => $message->getMessageType(),
                    'provider' => $message->getProvider(),
                    'status' => match ($message->getProvider()) {
                        'llm_unavailable' => 'llm_unavailable',
                        'technical_error' => 'technical_error',
                        null => null,
                        'openai' => 'llm_primary',
                        default => $message->isFallbackUsed() ? 'llm_secondary' : 'llm_primary',
                    },
                    'actions' => $this->messageActions($message),
                    'sources' => $message->getSourceUrls(),
                    'sourceCards' => $this->publicContentCatalog->findCardsByUrls($message->getSourceUrls()),
                    'createdAt' => $message->getCreatedAt()?->format(DATE_ATOM),
                ],
                $conversation->getMessages()->toArray()
            ),
            'leadSubmitted' => $conversation->getStatus() === ChatConversation::STATUS_SUBMITTED,
            'requestLead' => $conversation->getStatus() === ChatConversation::STATUS_LEAD_PENDING,
            'qualification' => $conversation->getQualification(),
            'contact' => $conversation->getLead() ? [
                'fullName' => $conversation->getLead()?->getFullName(),
                'email' => $conversation->getLead()?->getEmail(),
                'phone' => $conversation->getLead()?->getPhone(),
                'company' => $conversation->getLead()?->getCompany(),
            ] : null,
        ];
    }

    /**
     * @return array<int, array{type:string,label:string}>
     */
    private function messageActions(ChatMessage $message): array
    {
        if ($message->getRole() !== 'assistant') {
            return [];
        }

        $content = mb_strtolower((string) $message->getContent());
        $actions = [];

        if ($message->getMessageType() === 'diagnostic') {
            $actions[] = [
                'type' => 'generate_scoping_note',
                'label' => 'Préparer ma note de cadrage',
            ];
            $actions[] = [
                'type' => 'open_lead_form',
                'label' => 'Échanger avec un consultant OLING',
            ];
        } elseif (str_contains($content, 'mini-diagnostic')) {
            $actions[] = [
                'type' => 'start_diagnostic',
                'label' => 'Lancer un mini-diagnostic',
            ];
        }

        if (str_contains($content, 'note de cadrage') || $message->getMessageType() === 'scoping_note') {
            $actions[] = [
                'type' => $message->getMessageType() === 'scoping_note' ? 'download_scoping_note' : 'generate_scoping_note',
                'label' => $message->getMessageType() === 'scoping_note' ? 'Télécharger la note' : 'Préparer ma note de cadrage',
            ];
        }

        $contactIntent = in_array($message->getMessageType(), ['contact_offer', 'proposal_request', 'lead_request', 'contact_info', 'technical_unavailable'], true)
            || str_contains($content, '/contact?chat_fallback=1')
            || str_contains($content, 'contact@oling.fr')
            || str_contains($content, 'contacter oling')
            || str_contains($content, 'prise de contact');

        if ($contactIntent) {
            $label = match (true) {
                $message->getMessageType() === 'proposal_request' => 'Recevoir une proposition OLING',
                $message->getMessageType() === 'technical_unavailable' && str_contains($this->normalize($message->getContent()), 'proposition adapt') => 'Transmettre ma demande de proposition à OLING',
                $message->getMessageType() === 'contact_offer' => 'Être recontacté par OLING',
                default => 'Transmettre mon projet à OLING',
            };
            $actions[] = [
                'type' => 'open_lead_form',
                'label' => $label,
            ];
        }

        return array_values(array_unique($actions, SORT_REGULAR));
    }

    private function addVisitorMessage(ChatConversation $conversation, string $content): void
    {
        $message = (new ChatMessage())
            ->setRole('visitor')
            ->setMessageType('answer')
            ->setContent($content)
            ->setSequenceNumber($conversation->getMessages()->count() + 1)
            ->setCreatedAt(new \DateTimeImmutable());

        $conversation->addMessage($message);
        $this->entityManager->persist($message);
    }

    /**
     * @param string[] $sources
     */
    private function addAssistantMessage(ChatConversation $conversation, string $content, string $type, array $sources = [], ?ChatReply $reply = null): void
    {
        $message = (new ChatMessage())
            ->setRole('assistant')
            ->setMessageType($type)
            ->setContent($content)
            ->setSourceUrls($sources)
            ->setProvider($reply?->provider)
            ->setModel($reply?->model)
            ->setFallbackUsed($reply?->fallbackUsed ?? false)
            ->setOwnerUrl($reply?->ownerUrl)
            ->setSelectedDocuments($reply?->selectedDocuments)
            ->setLatencyMs($reply?->latencyMs)
            ->setInputTokens($reply?->inputTokens)
            ->setOutputTokens($reply?->outputTokens)
            ->setErrorCode($reply?->errorCode)
            ->setRequestId($reply?->requestId)
            ->setSequenceNumber($conversation->getMessages()->count() + 1)
            ->setCreatedAt(new \DateTimeImmutable());

        $conversation->addMessage($message);
        $this->entityManager->persist($message);
    }

    private function touchConversation(ChatConversation $conversation): void
    {
        $now = new \DateTimeImmutable();
        $conversation->setLastMessageAt($now);

        if ($conversation->getStatus() === ChatConversation::STATUS_SUBMITTED) {
            return;
        }

        $conversation
            ->setExpiresAt($now->modify('+30 days'))
            ->setRetentionPurgeAt($now->modify('+30 days'));
    }

    private function normalizeSerializedMessageContent(ChatMessage $message): string
    {
        if ($message->getRole() === 'assistant' && $message->getMessageType() === 'welcome') {
            $content = trim($message->getContent());

            if (str_contains($content, 'Constat') && str_contains($content, 'Prochaine étape')) {
                return $this->contentProvider->text('copy.legacy_welcome', $message->getConversation()?->getLocale() ?: AiConsultantContentProvider::LOCALE);
            }
        }

        return $message->getContent();
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower($value);
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $value = is_string($ascii) ? $ascii : $value;

        return preg_replace('/[^a-z0-9]+/', ' ', $value) ?? $value;
    }
}

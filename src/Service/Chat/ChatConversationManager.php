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
    ) {
    }

    public function createConversation(?string $sourcePath, ?string $sourceUrl, ?string $referrer, ?string $locale, ?string $ip, ?string $userAgent): ChatConversation
    {
        $conversation = (new ChatConversation())
            ->setPublicToken(bin2hex(random_bytes(24)))
            ->setStatus(ChatConversation::STATUS_ACTIVE)
            ->setSourcePath($sourcePath)
            ->setSourceUrl($sourceUrl)
            ->setReferrer($referrer)
            ->setLocale($locale ?: 'fr')
            ->setIpHash($ip ? hash('sha256', $ip) : null)
            ->setUserAgentHash($userAgent ? hash('sha256', $userAgent) : null);

        $this->entityManager->persist($conversation);
        $this->addAssistantMessage($conversation, $this->chatResponder->getWelcomeMessage(), 'welcome');
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
        $this->addAssistantMessage($conversation, $reply->content, $reply->messageType, $reply->sources);

        $conversation->setQualification($qualification);
        $conversation->setStatus($reply->requestLead ? ChatConversation::STATUS_LEAD_PENDING : ChatConversation::STATUS_ACTIVE);
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

        if ($fullName === '' || $email === '' || $phone === '' || $company === '' || $needDescription === '') {
            throw new \InvalidArgumentException('Tous les champs sont obligatoires.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('L’email n’est pas valide.');
        }

        if (!$rgpdConsent) {
            throw new \InvalidArgumentException('Le consentement RGPD est obligatoire.');
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
            'Merci, votre demande a bien été envoyée. Un consultant OLING reviendra vers vous rapidement. Si vous le souhaitez, vous pouvez aussi continuer ici et poser une autre question.',
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
                    'content' => $message->getContent(),
                    'type' => $message->getMessageType(),
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
    private function addAssistantMessage(ChatConversation $conversation, string $content, string $type, array $sources = []): void
    {
        $message = (new ChatMessage())
            ->setRole('assistant')
            ->setMessageType($type)
            ->setContent($content)
            ->setSourceUrls($sources)
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
}

<?php

namespace App\Service\Chat;

use App\Entity\ChatConversation;
use App\Service\Chat\Ai\AiDecision;
use App\Service\Chat\Ai\AiProviderInterface;
use App\Service\Chat\Ai\HeuristicAiProvider;
use Psr\Log\LoggerInterface;

class ChatResponder
{
    /**
     * @param iterable<AiProviderInterface> $providers
     */
    public function __construct(
        private readonly PublicContentCatalog $publicContentCatalog,
        private readonly ChatQualificationService $qualificationService,
        private readonly HeuristicAiProvider $heuristicProvider,
        private readonly iterable $providers,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function getWelcomeMessage(): string
    {
        return 'Bonjour. Je peux vous aider à clarifier votre besoin. Décrivez simplement votre contexte, votre enjeu ou votre point de blocage.';
    }

    public function reply(ChatConversation $conversation, string $visitorMessage): ChatReply
    {
        $qualification = $this->qualificationService->qualify($conversation);
        $documents = $this->shouldSkipDocumentLookup($visitorMessage, $qualification)
            ? []
            : $this->findRelevantDocumentsSafely($conversation, $visitorMessage, $qualification);

        foreach ($this->providers as $provider) {
            if (!$provider->isAvailable()) {
                continue;
            }

            try {
                $decision = $provider->generateDecision($conversation, $visitorMessage, $documents, $qualification);
                return $this->createReplyFromDecision($conversation, $visitorMessage, $documents, $qualification, $decision, $provider->getName());
            } catch (\Throwable $exception) {
                $this->logger->warning('Chat provider failed.', [
                    'provider' => $provider->getName(),
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        try {
            $decision = $this->heuristicProvider->generateDecision($conversation, $visitorMessage, $documents, $qualification);

            return $this->createReplyFromDecision($conversation, $visitorMessage, $documents, $qualification, $decision, $this->heuristicProvider->getName());
        } catch (\Throwable $exception) {
            $this->logger->error('Heuristic chat fallback failed.', [
                'error' => $exception->getMessage(),
            ]);
        }

        return $this->createEmergencyReply($conversation, $visitorMessage, $documents, $qualification);
    }

    /**
     * @param array<int, array{title:string,url:string,text:string,type:string,image:?string,excerpt:string}> $documents
     * @param array<string, string|null> $qualification
     */
    private function createReplyFromDecision(
        ChatConversation $conversation,
        string $visitorMessage,
        array $documents,
        array $qualification,
        AiDecision $decision,
        ?string $provider
    ): ChatReply {
        $mergedQualification = $this->qualificationService->qualify($conversation, $decision->qualification ?: $qualification);
        $contactStep = $this->resolveContactStep($conversation, $visitorMessage, $mergedQualification, $decision->requestLead);

        return new ChatReply(
            $this->finalizeReply($decision->reply, $contactStep),
            $this->shouldShowLeadForm($contactStep),
            $this->filterSources($documents, $visitorMessage, $mergedQualification),
            $mergedQualification,
            $provider,
            $contactStep
        );
    }

    /**
     * @param array<string, string|null> $qualification
     * @return array<int, array{title:string,url:string,text:string,type:string,image:?string,excerpt:string}>
     */
    private function findRelevantDocumentsSafely(ChatConversation $conversation, string $visitorMessage, array $qualification): array
    {
        try {
            $qualificationTerms = array_values(array_filter(
                $qualification,
                static fn (mixed $value): bool => is_string($value) && $value !== ''
            ));

            return $this->publicContentCatalog->findRelevantDocuments(
                trim($visitorMessage.' '.implode(' ', $qualificationTerms)),
                $conversation->getSourcePath(),
                2
            );
        } catch (\Throwable $exception) {
            $this->logger->warning('Public content catalog lookup failed.', [
                'error' => $exception->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * @param array<int, array{title:string,url:string,text:string,type:string,image:?string,excerpt:string}> $documents
     * @param array<string, string|null> $qualification
     */
    private function createEmergencyReply(
        ChatConversation $conversation,
        string $visitorMessage,
        array $documents,
        array $qualification
    ): ChatReply {
        $contactStep = $this->resolveContactStep($conversation, $visitorMessage, $qualification, false);
        $reply = $this->qualificationService->isTooVague($qualification)
            ? 'Je peux vous aider à cadrer le sujet. Quel est surtout votre enjeu aujourd’hui: outil, organisation, conformité ou risque ?'
            : 'Je peux vous aider à clarifier le besoin. Quel est le point le plus important à sécuriser dans votre contexte actuel ?';

        return new ChatReply(
            $this->finalizeReply($reply, $contactStep),
            $this->shouldShowLeadForm($contactStep),
            $this->filterSources($documents, $visitorMessage, $qualification),
            $qualification,
            'emergency_fallback',
            $contactStep
        );
    }

    /**
     * @param array<int, array{title:string,url:string,text:string,type:string,image:?string,excerpt:string}> $documents
     * @param array<string, string|null> $qualification
     * @return string[]
     */
    private function filterSources(array $documents, string $visitorMessage, array $qualification): array
    {
        if ($documents === []) {
            return [];
        }

        if ($this->showsStrongContactIntent($visitorMessage)) {
            return [];
        }

        if (mb_strlen(trim($visitorMessage)) < 18 && $this->qualificationService->isTooVague($qualification)) {
            return [];
        }

        return array_column(array_slice($documents, 0, 2), 'url');
    }

    /**
     * @param array<string, string|null> $qualification
     */
    private function resolveContactStep(ChatConversation $conversation, string $visitorMessage, array $qualification, bool $providerRequestsLead): string
    {
        if ($this->showsDirectContactQuestion($visitorMessage)) {
            return 'contact_info';
        }

        if ($this->showsStrongContactIntent($visitorMessage)) {
            return 'lead_request';
        }

        if ($this->hasPendingContactOffer($conversation) && $this->isPositiveReply($visitorMessage)) {
            return 'lead_request';
        }

        if ($providerRequestsLead && $this->shouldOfferContact($conversation, $qualification)) {
            return 'contact_offer';
        }

        if ($this->shouldOfferContact($conversation, $qualification)) {
            return 'contact_offer';
        }

        return 'question';
    }

    /**
     * @param array<string, string|null> $qualification
     */
    private function shouldOfferContact(ChatConversation $conversation, array $qualification): bool
    {
        if (!$this->qualificationService->isReadyForLead($qualification, $conversation)) {
            return false;
        }

        if ($this->hasPendingContactOffer($conversation)) {
            return false;
        }

        return $this->countVisitorMessages($conversation) >= 2;
    }

    private function hasPendingContactOffer(ChatConversation $conversation): bool
    {
        $messages = $conversation->getMessages()->toArray();
        $lastAssistant = null;

        for ($index = count($messages) - 1; $index >= 0; --$index) {
            $message = $messages[$index];
            if ($message->getRole() === 'assistant') {
                $lastAssistant = $message;
                break;
            }
        }

        return $lastAssistant?->getMessageType() === 'contact_offer';
    }

    private function countVisitorMessages(ChatConversation $conversation): int
    {
        $count = 0;
        foreach ($conversation->getMessages() as $message) {
            if ($message->getRole() === 'visitor') {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * @param array<string, string|null> $qualification
     */
    private function shouldSkipDocumentLookup(string $visitorMessage, array $qualification): bool
    {
        if ($this->showsDirectContactQuestion($visitorMessage)) {
            return true;
        }

        return mb_strlen(trim($visitorMessage)) < 24 && $this->qualificationService->isTooVague($qualification);
    }

    private function showsStrongContactIntent(string $message): bool
    {
        $text = $this->normalize($message);

        return (bool) preg_match('/\b(contact|me contacter|vous contacter|etre contacte|je veux etre contacte|contactez moi|contactez-nous|contact direct|prise de contact|contacter un consultant|parler a quelqu|parler avec un consultant|echange avec un consultant|etre rappele|rappelez moi|rappel|rdv|rendez vous|formulaire de contact|page contact|aller au contact|aller sur le formulaire|ouvrir le formulaire|devis|audit|accompagnement|proposition d accompagnement|proposition commerciale|je veux une proposition|appelez moi)\b/', $text);
    }

    private function showsDirectContactQuestion(string $message): bool
    {
        $text = $this->normalize($message);

        if (!preg_match('/\b(tel|telephone|numero|mail|email|e-mail|joindre|contacter|contact)\b/', $text)) {
            return false;
        }

        return (bool) preg_match('/\b(votre|vos|comment|quel|quelle|joindre|contacter|contact)\b/', $text);
    }

    private function isPositiveReply(string $message): bool
    {
        $text = trim($this->normalize($message));

        return $text !== '' && (bool) preg_match('/^(oui|oui volontiers|oui bien sur|ok|ok pour un echange|d accord|je veux bien|volontiers|avec plaisir|why not|yes|allons y|go)\b/', $text);
    }

    private function finalizeReply(string $reply, string $contactStep): string
    {
        $reply = trim($reply);
        $reply = preg_replace('/\s+/', ' ', $reply) ?? $reply;

        if ($contactStep === 'contact_info') {
            return $this->contactDetailsText(false);
        }

        if ($contactStep === 'contact_offer') {
            $reply = rtrim($reply, " \t\n\r\0\x0B?.!");

            return $reply.'. '.$this->contactDetailsText(false);
        }

        if ($contactStep === 'lead_request') {
            return 'Très bien. '.$this->contactDetailsText(true);
        }

        return $reply;
    }

    private function contactDetailsText(bool $includeLeadForm): string
    {
        if ($includeLeadForm) {
            return 'Pour joindre OLING rapidement, appelez le 01 89 70 15 60 ou écrivez à contact@oling.fr. Je peux aussi transmettre votre demande à un consultant OLING via le formulaire ci-dessous. Adresse publique : 40 rue Alexandre Dumas, 75011 Paris.';
        }

        return 'Si vous souhaitez contacter OLING, appelez le 01 89 70 15 60 ou écrivez à contact@oling.fr. Je peux aussi vous proposer un échange via le formulaire.';
    }

    private function shouldShowLeadForm(string $contactStep): bool
    {
        return in_array($contactStep, ['contact_info', 'contact_offer', 'lead_request'], true);
    }

    private function normalize(string $value): string
    {
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($normalized === false) {
            $normalized = $value;
        }

        return strtolower($normalized);
    }
}

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
        return 'Bonjour. Je suis l’assistant expert OLING. Posez une question sur nos expertises, nos expériences, notre équipe ou votre projet.';
    }

    public function reply(ChatConversation $conversation, string $visitorMessage): ChatReply
    {
        $startedAt = microtime(true);
        $qualification = $this->qualificationService->qualify($conversation);

        if ($this->asksForNamedClientOrClientList($visitorMessage)) {
            return new ChatReply(
                $this->confidentialityRefusal($visitorMessage),
                false,
                [],
                $qualification,
                'confidentiality_guard',
                'question'
            );
        }

        $lookupStartedAt = microtime(true);
        $documents = $this->shouldSkipDocumentLookup($visitorMessage, $qualification)
            ? []
            : $this->findRelevantDocumentsSafely($conversation, $visitorMessage, $qualification);
        $retrievalDurationMs = (int) round((microtime(true) - $lookupStartedAt) * 1000);

        foreach ($this->providers as $provider) {
            if (!$provider->isAvailable()) {
                continue;
            }

            try {
                $providerStartedAt = microtime(true);
                $decision = $provider->generateDecision($conversation, $visitorMessage, $documents, $qualification);
                $reply = $this->createReplyFromDecision($conversation, $visitorMessage, $documents, $qualification, $decision, $provider->getName());
                $this->logTechnicalMetrics($visitorMessage, $documents, $reply, $provider->getName(), $retrievalDurationMs, (int) round((microtime(true) - $providerStartedAt) * 1000), (int) round((microtime(true) - $startedAt) * 1000), false);

                return $reply;
            } catch (\Throwable $exception) {
                $this->logger->warning('Chat provider failed.', [
                    'provider' => $provider->getName(),
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        try {
            $providerStartedAt = microtime(true);
            $decision = $this->heuristicProvider->generateDecision($conversation, $visitorMessage, $documents, $qualification);
            $reply = $this->createReplyFromDecision($conversation, $visitorMessage, $documents, $qualification, $decision, $this->heuristicProvider->getName());
            $this->logTechnicalMetrics($visitorMessage, $documents, $reply, $this->heuristicProvider->getName(), $retrievalDurationMs, (int) round((microtime(true) - $providerStartedAt) * 1000), (int) round((microtime(true) - $startedAt) * 1000), true);

            return $reply;
        } catch (\Throwable $exception) {
            $this->logger->error('Heuristic chat fallback failed.', [
                'error' => $exception->getMessage(),
            ]);
        }

        $reply = $this->createEmergencyReply($conversation, $visitorMessage, $documents, $qualification);
        $this->logTechnicalMetrics($visitorMessage, $documents, $reply, 'emergency_fallback', $retrievalDurationMs, 0, (int) round((microtime(true) - $startedAt) * 1000), true);

        return $reply;
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
            $this->applyFinalSafetyGuard($this->finalizeReply($decision->reply, $contactStep)),
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
            $primaryDocuments = $this->publicContentCatalog->findRelevantDocuments(
                $visitorMessage,
                $conversation->getSourcePath(),
                6
            );

            if ($qualificationTerms === []) {
                return $primaryDocuments;
            }

            $expandedDocuments = $this->publicContentCatalog->findRelevantDocuments(
                trim($visitorMessage.' '.implode(' ', $qualificationTerms)),
                $conversation->getSourcePath(),
                8
            );

            return array_slice($this->mergeDocumentsByUrl($primaryDocuments, $expandedDocuments), 0, 8);
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
            $this->applyFinalSafetyGuard($this->finalizeReply($reply, $contactStep)),
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

        $expertIntent = $this->isExpertIntent($visitorMessage);
        $referenceIntent = $this->isReferenceIntent($visitorMessage);
        $preferredTypes = $expertIntent
            ? ['team', 'expertise', 'service', 'page', 'reference']
            : ($referenceIntent ? ['reference', 'expertise', 'service', 'page'] : ['expertise', 'service', 'page', 'reference']);

        $filtered = array_values(array_filter(
            $documents,
            static fn (array $document): bool => $expertIntent || $document['type'] !== 'team'
        ));

        usort($filtered, function (array $left, array $right) use ($preferredTypes, $visitorMessage): int {
            $leftRank = array_search($left['type'], $preferredTypes, true);
            $rightRank = array_search($right['type'], $preferredTypes, true);
            $leftScore = $this->sourceDisplayScore($left, $visitorMessage);
            $rightScore = $this->sourceDisplayScore($right, $visitorMessage);

            if ($leftScore !== $rightScore) {
                return $rightScore <=> $leftScore;
            }

            return ($leftRank === false ? 99 : $leftRank) <=> ($rightRank === false ? 99 : $rightRank);
        });

        $urls = [];
        $hasReference = false;
        foreach ($filtered as $document) {
            if ($document['type'] === 'reference') {
                if ($hasReference) {
                    continue;
                }
                $hasReference = true;
            }

            $urls[] = $document['url'];
            if (count($urls) === 2) {
                break;
            }
        }

        return array_values(array_unique($urls));
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
        if ($this->asksForNamedClientOrClientList($visitorMessage)) {
            return true;
        }

        return $this->showsDirectContactQuestion($visitorMessage);
    }

    private function showsStrongContactIntent(string $message): bool
    {
        $text = $this->normalize($message);

        return (bool) preg_match('/\b(contactez moi|je souhaite etre contacte|etre rappele|rappelez moi|prendre rendez vous|rendez vous|rdv|parler avec quelqu un|parler a un consultant|je souhaite une proposition|proposition commerciale|demande de devis|faites moi un devis|je veux un rendez vous|je veux etre contacte|appelez moi)\b/', $text);
    }

    private function showsDirectContactQuestion(string $message): bool
    {
        $text = $this->normalize($message);
        $compact = str_replace(' ', '', $text);

        $hasContactTerm = preg_match('/\b(tel|telephone|numero|mail|email|e-mail|joindre|contacter|contact)\b/', $text) === 1
            || str_contains($compact, 'telephone')
            || str_contains($compact, 'numero')
            || str_contains($compact, 'email')
            || str_contains($compact, 'contact');

        if (!$hasContactTerm) {
            return false;
        }

        return preg_match('/\b(votre|vos|comment|quel|quelle|joindre|contacter|contact)\b/', $text) === 1
            || str_contains($compact, 'votre')
            || str_contains($compact, 'comment');
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

    private function asksForNamedClientOrClientList(string $message): bool
    {
        $text = $this->normalize($message);

        if (preg_match('/\b(quels sont vos clients|donnez moi vos principaux clients|principaux clients|noms de clients)\b/', $text) === 1) {
            return true;
        }

        if (preg_match('/\bavez vous\b.*\bavec\b/', $text) === 1) {
            return true;
        }

        return preg_match('/\b(travaille[- ]avec|travaille[- ]pour|avez[- ]vous accompagne|quel port accompagnez[- ]vous|quel client)\b/', $text) === 1;
    }

    private function confidentialityRefusal(string $message): string
    {
        $text = $this->normalize($message);

        if (preg_match('/\b(client|clients)\b/', $text) === 1) {
            return 'Je ne cite pas les noms de clients dans mes réponses. Je peux en revanche vous présenter les types de missions, les secteurs concernés, les technologies utilisées et les problématiques traitées.';
        }

        return 'Je ne confirme ni ne détaille les relations avec des organisations nommées. Je peux en revanche vous indiquer les expériences OLING pertinentes sur ce type de contexte.';
    }

    private function applyFinalSafetyGuard(string $reply): string
    {
        $normalized = $this->normalize($reply);

        if (preg_match('/\b(client|clients)\b/', $normalized) === 1 && preg_match('/\b(nom|noms|liste)\b/', $normalized) === 1) {
            return 'Je peux décrire les contextes, missions et expertises OLING pertinentes, sans citer de nom de client.';
        }

        return $reply;
    }

    private function logTechnicalMetrics(
        string $visitorMessage,
        array $documents,
        ChatReply $reply,
        string $provider,
        int $retrievalDurationMs,
        int $providerDurationMs,
        int $totalDurationMs,
        bool $fallbackUsed
    ): void {
        $this->logger->info('Chat technical metrics.', [
            'intent' => $this->classificationIntent($visitorMessage),
            'retrieval_count' => count($documents),
            'retrieval_duration_ms' => $retrievalDurationMs,
            'provider' => $provider,
            'provider_duration_ms' => $providerDurationMs,
            'total_duration_ms' => $totalDurationMs,
            'fallback_used' => $fallbackUsed,
            'contact_step' => $reply->messageType,
        ]);
    }

    private function classificationIntent(string $message): string
    {
        if ($this->showsStrongContactIntent($message) || $this->showsDirectContactQuestion($message)) {
            return 'contact';
        }

        if ($this->asksForNamedClientOrClientList($message)) {
            return 'confidentiality';
        }

        $text = $this->normalize($message);

        if (preg_match('/\b(nous devons|nous voulons|notre|projet|remplacer|cahier des charges|consultation|probleme|obsolet)\b/', $text) === 1) {
            return 'project';
        }

        return 'information';
    }

    private function isExpertIntent(string $message): bool
    {
        return preg_match('/\b(qui|quel expert|quels experts|expert|consultant|equipe|profil)\b/', $this->normalize($message)) === 1;
    }

    private function isReferenceIntent(string $message): bool
    {
        return preg_match('/\b(reference|references|realisation|realisations|experience|experiences|secteur)\b/', $this->normalize($message)) === 1;
    }

    /**
     * @param array{title:string,url:string,text:string,type:string,image:?string,excerpt:string} $document
     */
    private function sourceDisplayScore(array $document, string $visitorMessage): int
    {
        $queryTokens = $this->queryTokens($visitorMessage);
        if ($queryTokens === []) {
            return 0;
        }

        $titleTokens = $this->tokenSet((string) ($document['title'] ?? ''));
        $textTokens = $this->tokenSet((string) ($document['text'] ?? ''));
        $urlTokens = $this->tokenSet((string) ($document['url'] ?? ''));
        $score = 0;
        foreach ($queryTokens as $token) {
            if (isset($textTokens[$token])) {
                $score += 3;
            }
            if (isset($titleTokens[$token])) {
                $score += 4;
            }
            if (isset($urlTokens[$token])) {
                $score += 2;
            }
        }

        if (($document['type'] ?? null) === 'reference' && $this->isReferenceIntent($visitorMessage)) {
            $score += 4;
        }

        if (($document['type'] ?? null) === 'team' && $this->isExpertIntent($visitorMessage)) {
            $score += 10;
        }

        return $score;
    }

    /**
     * @return string[]
     */
    private function queryTokens(string $message): array
    {
        $tokens = preg_split('/[^a-z0-9]+/i', $this->normalize($message)) ?: [];
        $tokens = array_values(array_filter($tokens, function (string $token): bool {
            if (strlen($token) < 3) {
                return false;
            }

            return !in_array($token, [
                'quel',
                'quelle',
                'quels',
                'quelles',
                'avec',
                'dans',
                'pour',
                'vous',
                'votre',
                'notre',
                'offre',
                'sujet',
            ], true);
        }));

        return array_values(array_unique(array_slice($tokens, 0, 10)));
    }

    /**
     * @return array<string, true>
     */
    private function tokenSet(string $value): array
    {
        $tokens = preg_split('/[^a-z0-9]+/i', $this->normalize($value)) ?: [];
        $set = [];
        foreach ($tokens as $token) {
            if ($token === '') {
                continue;
            }

            $set[$token] = true;
        }

        return $set;
    }

    /**
     * @param array<int, array{title:string,url:string,text:string,type:string,image:?string,excerpt:string}> ...$sets
     * @return array<int, array{title:string,url:string,text:string,type:string,image:?string,excerpt:string}>
     */
    private function mergeDocumentsByUrl(array ...$sets): array
    {
        $merged = [];
        foreach ($sets as $documents) {
            foreach ($documents as $document) {
                $url = $document['url'] ?? null;
                if (!is_string($url) || $url === '' || isset($merged[$url])) {
                    continue;
                }

                $merged[$url] = $document;
            }
        }

        return array_values($merged);
    }

    private function normalize(string $value): string
    {
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($normalized === false) {
            $normalized = $value;
        }

        $normalized = strtolower($normalized);

        return trim(preg_replace('/[^a-z0-9]+/', ' ', $normalized) ?? $normalized);
    }
}

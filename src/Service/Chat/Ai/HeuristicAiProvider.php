<?php

namespace App\Service\Chat\Ai;

use App\Entity\ChatConversation;
use App\Service\Chat\ChatQualificationService;

class HeuristicAiProvider implements AiProviderInterface
{
    public function __construct(private readonly ChatQualificationService $qualificationService)
    {
    }

    public function getName(): string
    {
        return 'heuristic';
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function generateDecision(
        ChatConversation $conversation,
        string $visitorMessage,
        array $documents,
        array $qualification
    ): AiDecision {
        $missingFields = $qualification['missing_fields'] ?? [];
        if (!is_array($missingFields)) {
            $missingFields = [];
        }

        $qualification = $this->qualificationService->sanitizeQualification($qualification);
        $reply = trim($this->buildReply($visitorMessage, $documents, $qualification, $missingFields));

        return new AiDecision(
            $reply,
            false,
            $qualification,
            array_column($documents, 'url'),
            $missingFields,
            null,
            $this->getName()
        );
    }

    /**
     * @param array<int, array{title:string,url:string,text:string,type:string}> $documents
     * @param array<string, string|null> $qualification
     * @param string[] $missingFields
     */
    private function buildReply(string $visitorMessage, array $documents, array $qualification, array $missingFields): string
    {
        if ($this->asksForNamedClientOrClientList($visitorMessage)) {
            return $this->confidentialityRefusal($visitorMessage);
        }

        if ($this->isDirectContactQuestion($visitorMessage)) {
            return $this->formatBulletReply(
                'Si vous souhaitez contacter OLING :',
                [
                    'Appelez le 01 89 70 15 60',
                    'Écrivez à contact@oling.fr',
                    'Je peux aussi vous proposer un échange via le formulaire',
                ]
            );
        }

        if ($this->looksLikeGreeting($visitorMessage) && $this->qualificationService->isTooVague($qualification)) {
            return 'Bonjour.';
        }

        if ($this->isAmoaIso27001Blend($visitorMessage)) {
            return 'Vous semblez croiser un sujet d’AMOA et de structuration ISO 27001. Cherchez-vous surtout à cadrer une démarche ISO 27001, à choisir ou piloter un outil, ou à articuler les deux ?';
        }

        if ($this->asksForReferences($visitorMessage) || $this->shouldPreferReferenceReply($visitorMessage, $documents)) {
            return $this->buildReferenceReply($visitorMessage, $documents, $qualification);
        }

        if ($this->isInformationRequest($visitorMessage)) {
            return $this->buildInformationReply($visitorMessage, $documents, $qualification);
        }

        $analysis = $this->buildProjectAnalysis($documents, $qualification);
        $question = $this->nextUsefulQuestion($visitorMessage, $qualification, $missingFields);

        return trim($analysis.' '.$question);
    }

    /**
     * @param array<int, array{title:string,url:string,text:string,type:string}> $documents
     * @param array<string, string|null> $qualification
     */
    private function buildInformationReply(string $visitorMessage, array $documents, array $qualification): string
    {
        if ($documents !== []) {
            $reference = $this->firstDocumentOfType($documents, 'reference');
            $expertise = $this->firstDocumentOfTypes($documents, ['expertise', 'service', 'page']);
            $team = $this->firstDocumentOfType($documents, 'team');

            if ($this->asksForReferences($visitorMessage) && $reference !== null) {
                $reply = $this->formatBulletReply(
                    'Oui. OLING dispose de références anonymisées sur ce type de contexte.',
                    [$this->excerptSentence($reference['text'])]
                );
                if ($expertise !== null) {
                    $reply .= "\n".$this->bulletLine($this->bridgeSentence($expertise['text']));
                }

                return $reply;
            }

            if ($this->asksForExpert($visitorMessage) && $team !== null) {
                $reply = $this->formatBulletReply(
                    'Le profil OLING le plus pertinent dans ce contexte est :',
                    [$this->excerptSentence($team['text'])]
                );
                if ($expertise !== null) {
                    $reply .= "\n".$this->bulletLine($this->bridgeSentence($expertise['text']));
                }

                return $reply;
            }

            $first = $documents[0];
            $reply = $this->excerptSentence($first['text']);
            if ($expertise !== null && ($first['url'] ?? '') !== ($expertise['url'] ?? '')) {
                $reply .= ' '.$this->bridgeSentence($expertise['text']);
            }

            return $reply;
        }

        $primaryNeed = $qualification['primary_need'] ?? null;
        if ($primaryNeed !== null) {
            return match ($primaryNeed) {
                'amoa_erp' => 'OLING intervient en AMOA ERP et applicatifs métiers, du cadrage jusqu’au choix de solution, à la reprise de données, aux interfaces et à la recette.',
                'rgpd' => 'OLING intervient sur la gouvernance RGPD, le registre, les DPIA, l’organisation DPO et la mise sous contrôle opérationnelle.',
                'cybersecurite' => 'OLING intervient sur la sécurité des SI, la résilience, les analyses de risques et les cadres comme ISO 27001, NIS2 ou DORA.',
                'ia_data_automatisation' => 'OLING intervient sur la data, l’automatisation, le reporting et les usages de l’IA reliés aux besoins métier.',
                'conformite' => 'OLING intervient sur les dispositifs de conformité, de contrôle, de risques et de pilotage.',
                'organisation_gouvernance' => 'OLING intervient sur les sujets d’organisation, de gouvernance, de responsabilités et de pilotage.',
                'transformation_si' => 'OLING intervient sur les trajectoires de transformation SI, le cadrage, les arbitrages et la sécurisation de l’exécution.',
                default => '',
            };
        }

        return 'Je peux vous aider à identifier les expertises, expériences et ressources OLING les plus pertinentes.';
    }

    /**
     * @param array<int, array{title:string,url:string,text:string,type:string}> $documents
     * @param array<string, string|null> $qualification
     */
    private function buildReferenceReply(string $visitorMessage, array $documents, array $qualification): string
    {
        $referenceDocuments = array_values(array_filter(
            $documents,
            static fn (array $document): bool => ($document['type'] ?? '') === 'reference'
        ));

        if ($referenceDocuments !== []) {
            $lead = $this->excerptSentence($referenceDocuments[0]['text']);

            if ($this->mentionsAmoaProgiciel($visitorMessage)) {
                return $this->formatBulletReply(
                    'Oui. OLING dispose de références anonymisées sur ce type de contexte, y compris en AMOA progiciel.',
                    [$lead]
                );
            }

            return $this->formatBulletReply(
                'Oui. OLING dispose de références anonymisées sur ce type de contexte.',
                [$lead]
            );
        }

        if (($qualification['primary_need'] ?? null) === 'amoa_erp') {
            return 'OLING intervient en AMOA progiciel et peut présenter des références anonymisées par secteur, mission et contexte, sans citer de client.';
        }

        return 'Je peux présenter des références OLING de manière anonymisée, par secteur, mission, technologie et problématique traitée.';
    }

    /**
     * @param array<int, array{title:string,url:string,text:string,type:string}> $documents
     * @param array<string, string|null> $qualification
     */
    private function buildProjectAnalysis(array $documents, array $qualification): string
    {
        if ($documents !== []) {
            $lead = $this->excerptSentence($documents[0]['text']);
            $support = $this->firstDocumentOfTypes(array_slice($documents, 1), ['service', 'expertise', 'reference', 'team', 'page']);

            if ($support !== null) {
                return $this->formatBulletReply(
                    'Voici le point le plus pertinent pour votre sujet :',
                    [
                        $lead,
                        $this->bridgeSentence($support['text']),
                    ]
                );
            }

            return $lead;
        }

        $primaryNeed = $qualification['primary_need'] ?? null;

        return match ($primaryNeed) {
            'amoa_erp' => 'OLING intervient sur le cadrage et la sécurisation des projets ERP et applicatifs métiers.',
            'rgpd' => 'OLING peut intervenir sur la mise sous contrôle du dispositif RGPD, la gouvernance, les preuves et le plan d’actions.',
            'cybersecurite' => 'OLING peut intervenir sur l’analyse de risques, la gouvernance sécurité, la feuille de route et la remise sous contrôle du dispositif.',
            'conformite' => 'OLING peut intervenir sur le diagnostic, la cartographie des écarts, les priorités et le pilotage des actions.',
            default => 'OLING peut intervenir pour structurer le sujet, clarifier le périmètre et sécuriser la trajectoire de mise en œuvre.',
        };
    }

    /**
     * @param array<string, string|null> $qualification
     * @param string[] $missingFields
     */
    private function nextUsefulQuestion(string $visitorMessage, array $qualification, array $missingFields): string
    {
        if ($this->qualificationService->isTooVague($qualification)) {
            return 'Pour bien cadrer: votre sujet porte surtout sur un outil, une organisation, une contrainte réglementaire ou un risque ?';
        }

        if ($this->isInformationRequest($visitorMessage)) {
            return '';
        }

        if (in_array('primary_need', $missingFields, true)) {
            return 'Le point clé est-il plutôt le choix de solution, le cadrage, la conformité, la sécurité ou l’organisation cible ?';
        }

        if (preg_match('/\b(remplacer|obsolete|obsolescent|migration)\b/', $this->normalize($visitorMessage)) === 1) {
            return 'Le point déterminant est de savoir si vous cherchez d’abord à remplacer l’outil, à remettre à plat les processus, ou à sécuriser les deux en parallèle.';
        }

        return 'Quel est le point métier ou opérationnel le plus important à sécuriser dans votre contexte ?';
    }

    private function looksLikeGreeting(string $message): bool
    {
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $message);
        if ($normalized === false) {
            $normalized = $message;
        }

        return (bool) preg_match('/^\s*(bonjour|bonsoir|salut|hello|coucou)\b/i', strtolower($normalized));
    }

    private function isShortKeywordQuery(string $message): bool
    {
        $normalized = $this->normalize($message);
        $tokens = preg_split('/[^a-z0-9]+/', $normalized) ?: [];
        $tokens = array_values(array_filter($tokens, static fn (string $token): bool => $token !== ''));

        return count($tokens) <= 4 && mb_strlen(trim($message)) <= 32;
    }

    private function isAmoaIso27001Blend(string $message): bool
    {
        $normalized = $this->normalize($message);

        return preg_match('/\b(amoa|amo|erp|applicatif|outil)\b/', $normalized) === 1
            && preg_match('/\b(iso27001|iso 27001|smsi|ssi|cyber|securite)\b/', $normalized) === 1;
    }

    private function normalize(string $message): string
    {
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $message);
        if ($normalized === false) {
            $normalized = $message;
        }

        $normalized = strtolower($normalized);

        return trim(preg_replace('/[^a-z0-9]+/', ' ', $normalized) ?? $normalized);
    }

    private function isInformationRequest(string $message): bool
    {
        $normalized = $this->normalize($message);

        if (preg_match('/\b(contactez moi|rendez vous|rdv|devis)\b/', $normalized) === 1) {
            return false;
        }

        if (preg_match('/\b(nous devons|nous voulons|notre|projet|remplacer|cahier des charges|consultation|probleme|obsolet)\b/', $normalized) === 1) {
            return false;
        }

        return preg_match('/\b(que|quoi|quelles|quelle|quels|qui|avez vous|connaissez vous|difference|faites vous)\b/', $normalized) === 1
            || $this->isShortKeywordQuery($message);
    }

    private function asksForReferences(string $message): bool
    {
        $text = $this->normalize($message);

        foreach (['reference', 'references', 'retour experience', 'retours experience', 'experience', 'experiences', 'secteur'] as $needle) {
            if (str_contains($text, $needle)) {
                return true;
            }
        }

        return str_contains($text, 'avez vous deja fait') || str_contains($text, 'avez vous deja accompagne');
    }

    private function mentionsAmoaProgiciel(string $message): bool
    {
        $text = $this->normalize($message);

        return preg_match('/\b(amoa|amo|progiciel|erp|gmao|crm|sirh|facturation)\b/', $text) === 1;
    }

    private function asksForExpert(string $message): bool
    {
        return preg_match('/\b(qui|quel expert|quels experts|expert|consultant|profil|equipe)\b/', $this->normalize($message)) === 1;
    }

    private function isDirectContactQuestion(string $message): bool
    {
        $text = $this->normalize($message);
        $compact = str_replace(' ', '', $text);

        $hasContactTerm = preg_match('/\b(tel|telephone|numero|mail|email|e-mail|joindre|contacter|contact)\b/', $text) === 1
            || str_contains($compact, 'telephone')
            || str_contains($compact, 'numero')
            || str_contains($compact, 'email')
            || str_contains($compact, 'contact');

        $hasQuestionTerm = preg_match('/\b(votre|vos|comment|quel|quelle|joindre|contacter|contact)\b/', $text) === 1
            || str_contains($compact, 'votre')
            || str_contains($compact, 'comment');

        return $hasContactTerm && $hasQuestionTerm;
    }

    /**
     * @param array<int, array{title:string,url:string,text:string,type:string}> $documents
     */
    private function shouldPreferReferenceReply(string $message, array $documents): bool
    {
        if ($documents === []) {
            return false;
        }

        $topType = $documents[0]['type'] ?? '';
        if ($topType !== 'reference') {
            return false;
        }

        $text = $this->normalize($message);

        return $this->isShortKeywordQuery($message)
            || str_contains($text, 'eau')
            || str_contains($text, 'assainissement')
            || $this->mentionsAmoaProgiciel($message);
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
            return 'Je ne cite pas les noms de clients dans mes réponses. Je peux en revanche présenter les types de missions, les secteurs concernés, les technologies utilisées et les problématiques traitées.';
        }

        return 'Je ne confirme ni ne détaille les relations avec des organisations nommées. Je peux en revanche indiquer les expériences OLING pertinentes sur ce type de contexte.';
    }

    private function excerptSentence(string $text): string
    {
        $clean = trim(preg_replace('/\s+/', ' ', $text) ?? $text);
        if ($clean === '') {
            return 'OLING dispose d’un retour d’expérience pertinent sur ce type de sujet.';
        }

        if (mb_strlen($clean) <= 220) {
            return $clean;
        }

        return rtrim(mb_substr($clean, 0, 217)).'...';
    }

    /**
     * @param array<int, array{title:string,url:string,text:string,type:string}> $documents
     * @return array{title:string,url:string,text:string,type:string}|null
     */
    private function firstDocumentOfType(array $documents, string $type): ?array
    {
        foreach ($documents as $document) {
            if (($document['type'] ?? null) === $type) {
                return $document;
            }
        }

        return null;
    }

    /**
     * @param array<int, array{title:string,url:string,text:string,type:string}> $documents
     * @param string[] $types
     * @return array{title:string,url:string,text:string,type:string}|null
     */
    private function firstDocumentOfTypes(array $documents, array $types): ?array
    {
        foreach ($documents as $document) {
            if (in_array($document['type'] ?? null, $types, true)) {
                return $document;
            }
        }

        return null;
    }

    private function bridgeSentence(string $text): string
    {
        return 'OLING intervient aussi sur '.$this->lowercaseFirst($this->excerptSentence($text));
    }

    /**
     * @param string[] $items
     */
    private function formatBulletReply(string $intro, array $items): string
    {
        $lines = [rtrim($intro)];
        foreach ($items as $item) {
            $item = trim($item);
            if ($item === '') {
                continue;
            }

            $lines[] = $this->bulletLine($item);
        }

        return implode("\n", $lines);
    }

    private function bulletLine(string $text): string
    {
        return '- '.$text;
    }

    private function lowercaseFirst(string $text): string
    {
        $first = mb_substr($text, 0, 1);

        return mb_strtolower($first).mb_substr($text, 1);
    }
}

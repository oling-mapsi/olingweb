<?php

namespace App\Service\Chat;

use App\Entity\ChatConversation;
use App\Entity\ChatLead;

class ChatSummaryService
{
    public function __construct(private readonly AiConsultantContentProvider $contentProvider)
    {
    }

    /**
     * @param array<string, string|null> $qualification
     * @return array{short:string,long:string}
     */
    public function build(ChatConversation $conversation, ChatLead $lead, array $qualification): array
    {
        $visitorMessages = [];
        foreach ($conversation->getMessages() as $message) {
            if ($message->getRole() === 'visitor') {
                $visitorMessages[] = trim((string) $message->getContent());
            }
        }

        $initialMessage = $visitorMessages[0] ?? $lead->getNeedDescription() ?? '';
        $locale = $conversation->getLocale() ?: AiConsultantContentProvider::LOCALE;
        $short = sprintf(
            $this->contentProvider->text('copy.summary_short', $locale),
            $lead->getCompany(),
            $this->contentProvider->label($qualification['primary_need'] ?? null, $locale),
            $this->contentProvider->label($qualification['urgency_level'] ?? null, $locale)
        );

        $lines = $this->contentProvider->map('copy.summary_lines', $locale);
        $long = trim(implode("\n", array_filter([
            sprintf($lines['initial_context'] ?? '%s', $initialMessage),
            sprintf($lines['primary_need'] ?? '%s', $this->contentProvider->label($qualification['primary_need'] ?? null, $locale)),
            sprintf($lines['urgency'] ?? '%s', $this->contentProvider->label($qualification['urgency_level'] ?? null, $locale)),
            sprintf($lines['maturity'] ?? '%s', $this->contentProvider->label($qualification['maturity_level'] ?? null, $locale)),
            sprintf($lines['organization_type'] ?? '%s', $this->contentProvider->label($qualification['organization_type'] ?? null, $locale)),
            sprintf($lines['organization_size'] ?? '%s', $this->contentProvider->label($qualification['organization_size'] ?? null, $locale)),
            sprintf($lines['commercial_intent'] ?? '%s', $this->contentProvider->label($qualification['commercial_intent'] ?? null, $locale)),
            sprintf($lines['potential_value'] ?? '%s', $this->contentProvider->label($qualification['potential_value'] ?? null, $locale)),
            sprintf($lines['description'] ?? '%s', $lead->getNeedDescription()),
            $this->erpAmoaSummary($conversation, $lead, $qualification),
        ])));

        return [
            'short' => $short,
            'long' => $long,
        ];
    }

    /**
     * @param array<string, string|null> $qualification
     */
    private function erpAmoaSummary(ChatConversation $conversation, ChatLead $lead, array $qualification): ?string
    {
        if (($qualification['primary_need'] ?? null) !== 'amoa_erp') {
            return null;
        }

        $text = mb_strtolower($this->normalize($lead->getNeedDescription().' '.$this->conversationText($conversation)));
        $modules = $this->matches($text, [
            'finance' => ['finance', 'comptabilite', 'facturation', 'controle de gestion'],
            'achats' => ['achat', 'achats', 'approvisionnement'],
            'ventes / CRM' => ['vente', 'commercial', 'crm', 'relation client'],
            'stocks / logistique' => ['stock', 'stocks', 'logistique', 'entrepot'],
            'production' => ['production', 'atelier', 'industrie'],
            'maintenance / GMAO' => ['maintenance', 'gmao', 'equipement'],
            'RH / SIRH' => ['rh', 'sirh', 'paie'],
            'reporting / BI' => ['reporting', 'bi', 'tableau de bord'],
        ]);
        $risks = $this->matches($text, [
            'reprise de données' => ['reprise', 'migration', 'donnees', 'donnee'],
            'interfaces' => ['interface', 'interfaces', 'api', 'flux'],
            'sécurité / RGPD' => ['securite', 'rgpd', 'habilitation', 'droits'],
            'adoption / conduite du changement' => ['adoption', 'formation', 'changement'],
            'planning / projet bloqué' => ['retard', 'bloque', 'bloquee', 'planning'],
        ]);

        $locale = $conversation->getLocale() ?: AiConsultantContentProvider::LOCALE;
        $patterns = $this->contentProvider->list('copy.erp_summary', $locale);

        return trim(implode("\n", [
            $patterns[0] ?? '',
            sprintf($patterns[1] ?? '%s', $modules === [] ? $this->contentProvider->text('copy.erp_modules_fallback', $locale) : implode(', ', $modules)),
            sprintf($patterns[2] ?? '%s', $risks === [] ? $this->contentProvider->text('copy.erp_risks_fallback', $locale) : implode(', ', $risks)),
            $patterns[3] ?? '',
            $patterns[4] ?? '',
        ]));
    }

    private function conversationText(ChatConversation $conversation): string
    {
        $parts = [];
        foreach ($conversation->getMessages() as $message) {
            if ($message->getRole() === 'visitor') {
                $parts[] = (string) $message->getContent();
            }
        }

        return implode(' ', $parts);
    }

    /**
     * @param array<string, string[]> $map
     * @return string[]
     */
    private function matches(string $text, array $map): array
    {
        $matches = [];
        foreach ($map as $label => $needles) {
            foreach ($needles as $needle) {
                if (str_contains($text, $needle)) {
                    $matches[] = $label;
                    break;
                }
            }
        }

        return $matches;
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

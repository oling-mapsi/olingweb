<?php

namespace App\Service\Chat;

use App\Entity\ChatConversation;
use App\Entity\ChatLead;

class ChatSummaryService
{
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
        $short = sprintf(
            '%s a sollicité OLING pour un besoin %s avec un niveau d’urgence %s.',
            $lead->getCompany(),
            $this->label($qualification['primary_need'] ?? null),
            $this->label($qualification['urgency_level'] ?? null)
        );

        $long = trim(implode("\n", array_filter([
            'Contexte initial : '.$initialMessage,
            'Besoin principal : '.$this->label($qualification['primary_need'] ?? null),
            'Urgence : '.$this->label($qualification['urgency_level'] ?? null),
            'Maturité : '.$this->label($qualification['maturity_level'] ?? null),
            'Type d’organisation : '.$this->label($qualification['organization_type'] ?? null),
            'Taille estimée : '.$this->label($qualification['organization_size'] ?? null),
            'Intention commerciale : '.$this->label($qualification['commercial_intent'] ?? null),
            'Valeur potentielle : '.$this->label($qualification['potential_value'] ?? null),
            'Description consolidée : '.$lead->getNeedDescription(),
        ])));

        return [
            'short' => $short,
            'long' => $long,
        ];
    }

    private function label(?string $value): string
    {
        return match ($value) {
            'amoa_erp' => 'AMOA ERP',
            'rgpd' => 'RGPD',
            'cybersecurite' => 'cybersécurité',
            'ia_data_automatisation' => 'IA / data / automatisation',
            'conformite' => 'conformité',
            'organisation_gouvernance' => 'organisation / gouvernance',
            'transformation_si' => 'transformation SI',
            'immediate' => 'immédiate',
            'short_term' => 'court terme',
            'planned' => 'planifiée',
            'exploratory' => 'exploratoire',
            'flou' => 'réflexion initiale',
            'cadre' => 'cadrage',
            'consultation' => 'consultation',
            'en_cours' => 'projet en cours',
            'bloque' => 'bloqué',
            'pme' => 'PME',
            'pmi' => 'PMI',
            'eti' => 'ETI',
            'public' => 'organisation publique',
            'association' => 'association',
            '1_49' => '1 à 49 personnes',
            '50_249' => '50 à 249 personnes',
            '250_999' => '250 à 999 personnes',
            '1000_plus' => '1000+ personnes',
            'diagnostic' => 'diagnostic',
            'cadrage' => 'cadrage',
            'assistance_projet' => 'assistance projet',
            'mise_en_conformite' => 'mise en conformité',
            'expertise_ponctuelle' => 'expertise ponctuelle',
            'orientation' => 'orientation',
            'high' => 'élevée',
            'medium' => 'moyenne',
            'low' => 'faible',
            default => $value ?? 'non qualifié',
        };
    }
}

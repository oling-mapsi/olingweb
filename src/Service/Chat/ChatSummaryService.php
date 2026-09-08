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
            $this->erpAmoaSummary($conversation, $lead, $qualification),
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

        return trim(implode("\n", [
            'Analyse AMOA ERP / progiciel :',
            'Modules pressentis : '.($modules === [] ? 'à qualifier' : implode(', ', $modules)).'.',
            'Points de vigilance : '.($risks === [] ? 'données, interfaces, sécurité, RGPD, recette et conduite du changement à qualifier' : implode(', ', $risks)).'.',
            'Livrables AMOA à envisager : note de cadrage, expression des besoins, cahier des charges ou grille de choix, stratégie de reprise, recette, conduite du changement.',
            'Prochaine étape OLING : échange de cadrage pour confirmer le périmètre, la maturité, le macro-planning, la charge et le budget indicatifs.',
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

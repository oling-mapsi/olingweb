<?php

namespace App\Service\Chat;

use App\Entity\ChatConversation;
use App\Entity\ChatLead;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class ChatLeadMailer
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
        private readonly string $recipient = 'florestan.rouet@oling.fr',
    ) {
    }

    /**
     * @param array<string, string|null> $qualification
     */
    public function send(ChatConversation $conversation, ChatLead $lead, array $qualification): void
    {
        $subject = sprintf(
            '[OLING][Lead IA] %s - %s - %s',
            $this->label($qualification['primary_need'] ?? null),
            $lead->getCompany(),
            $this->label($qualification['urgency_level'] ?? null)
        );

        $context = [
            'conversation' => $conversation,
            'lead' => $lead,
            'qualification' => $qualification,
        ];

        $email = (new Email())
            ->from('florestan.rouet@oling.fr')
            ->to($this->recipient)
            ->replyTo($lead->getEmail())
            ->subject($subject)
            ->text($this->twig->render('emails/chat_lead_notification.txt.twig', $context))
            ->html($this->twig->render('emails/chat_lead_notification.html.twig', $context));

        $this->mailer->send($email);
    }

    private function label(?string $value): string
    {
        return match ($value) {
            'amoa_erp' => 'AMOA ERP',
            'rgpd' => 'RGPD',
            'cybersecurite' => 'Cybersécurité',
            'ia_data_automatisation' => 'IA / Data',
            'conformite' => 'Conformité',
            'organisation_gouvernance' => 'Organisation',
            'transformation_si' => 'Transformation SI',
            'immediate' => 'immediate',
            'short_term' => 'short_term',
            'planned' => 'planned',
            'exploratory' => 'exploratory',
            default => $value ?? 'besoin non qualifié',
        };
    }
}

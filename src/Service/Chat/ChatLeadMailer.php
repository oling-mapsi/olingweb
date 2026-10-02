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
        private readonly AiConsultantContentProvider $contentProvider,
        private readonly string $recipient = 'florestan.rouet@oling.fr',
    ) {
    }

    /**
     * @param array<string, string|null> $qualification
     */
    public function send(ChatConversation $conversation, ChatLead $lead, array $qualification): void
    {
        $locale = $conversation->getLocale() ?: AiConsultantContentProvider::LOCALE;
        $subject = sprintf(
            $this->contentProvider->text('email.subject', $locale),
            $this->contentProvider->label($qualification['primary_need'] ?? null, $locale),
            $lead->getCompany(),
            $this->contentProvider->label($qualification['urgency_level'] ?? null, $locale)
        );

        $context = [
            'conversation' => $conversation,
            'lead' => $lead,
            'qualification' => $qualification,
            'chatContent' => $this->contentProvider->content($locale),
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

}

<?php

namespace App\Service\ErpQuestionnaire;

use App\Entity\ErpQuestionnaireSubmission;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class ErpQuestionnaireMailer
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
        private readonly ErpQuestionnairePdfGenerator $pdfGenerator,
        private readonly ErpQuestionnaireContentProvider $contentProvider,
        private readonly string $recipient = 'florestan.rouet@oling.fr',
    ) {
    }

    public function sendProspectAndInternal(ErpQuestionnaireSubmission $submission): void
    {
        $pdf = $this->pdfGenerator->generate($submission);
        $filename = $this->pdfGenerator->filename($submission);
        $content = $this->contentProvider->content($submission->getLocale());
        $context = [
            'submission' => $submission,
            'summary' => $submission->getSummary(),
            'answers' => $submission->getAnswers(),
            'scoring' => $submission->getScoring(),
            'erpContent' => $content,
        ];

        $prospect = (new Email())
            ->from('contact@oling.fr')
            ->to($submission->getEmail())
            ->subject($content['email']['prospect_subject'])
            ->text($this->twig->render('emails/erp_questionnaire_prospect.txt.twig', $context))
            ->html($this->twig->render('emails/erp_questionnaire_prospect.html.twig', $context))
            ->attach($pdf, $filename, 'application/pdf');

        $internal = (new Email())
            ->from('contact@oling.fr')
            ->to($this->recipient)
            ->replyTo($submission->getEmail())
            ->subject(sprintf($content['email']['internal_subject'], $submission->getCompany(), $submission->getUrgency() ?: $content['email']['internal_urgency_fallback']))
            ->text($this->twig->render('emails/erp_questionnaire_internal.txt.twig', $context))
            ->html($this->twig->render('emails/erp_questionnaire_internal.html.twig', $context))
            ->attach($pdf, $filename, 'application/pdf');

        $this->mailer->send($prospect);
        $this->mailer->send($internal);
    }
}

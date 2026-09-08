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
        private readonly string $recipient = 'florestan.rouet@oling.fr',
    ) {
    }

    public function sendProspectAndInternal(ErpQuestionnaireSubmission $submission): void
    {
        $pdf = $this->pdfGenerator->generate($submission);
        $filename = $this->pdfGenerator->filename($submission);
        $context = [
            'submission' => $submission,
            'summary' => $submission->getSummary(),
            'answers' => $submission->getAnswers(),
            'scoring' => $submission->getScoring(),
        ];

        $prospect = (new Email())
            ->from('contact@oling.fr')
            ->to($submission->getEmail())
            ->subject('Votre synthèse de qualification ERP / progiciel - OLING')
            ->text($this->twig->render('emails/erp_questionnaire_prospect.txt.twig', $context))
            ->html($this->twig->render('emails/erp_questionnaire_prospect.html.twig', $context))
            ->attach($pdf, $filename, 'application/pdf');

        $internal = (new Email())
            ->from('contact@oling.fr')
            ->to($this->recipient)
            ->replyTo($submission->getEmail())
            ->subject(sprintf('[OLING][Questionnaire ERP] %s - %s', $submission->getCompany(), $submission->getUrgency() ?: 'urgence à qualifier'))
            ->text($this->twig->render('emails/erp_questionnaire_internal.txt.twig', $context))
            ->html($this->twig->render('emails/erp_questionnaire_internal.html.twig', $context))
            ->attach($pdf, $filename, 'application/pdf');

        $this->mailer->send($prospect);
        $this->mailer->send($internal);
    }
}

<?php

namespace App\Service\ErpQuestionnaire;

use App\Entity\ErpQuestionnaireSubmission;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

class ErpQuestionnairePdfGenerator
{
    public function __construct(
        private readonly Environment $twig,
        private readonly string $projectDir,
    ) {
    }

    public function generate(ErpQuestionnaireSubmission $submission): string
    {
        $html = $this->twig->render('erp_questionnaire/pdf.html.twig', [
            'submission' => $submission,
            'summary' => $submission->getSummary(),
            'answers' => $submission->getAnswers(),
            'logoDataUri' => $this->logoDataUri(),
        ]);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);
        $options->set('chroot', $this->projectDir);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

        return (string) $dompdf->output();
    }

    public function filename(ErpQuestionnaireSubmission $submission): string
    {
        $company = preg_replace('/[^a-z0-9]+/i', '-', strtolower($submission->getCompany())) ?: 'prospect';

        return sprintf('qualification-erp-oling-%s.pdf', trim($company, '-'));
    }

    private function logoDataUri(): ?string
    {
        $path = $this->projectDir.'/public/img/logo/logoling.png';
        if (!is_file($path)) {
            return null;
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode($content);
    }
}

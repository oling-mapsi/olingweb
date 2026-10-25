<?php
namespace App\Twig;

use App\Service\QualificationContext;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class QualificationContextExtension extends AbstractExtension
{
    public function __construct(private RequestStack $requests) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('qualification_context', [$this, 'context']),
            new TwigFunction('qualification_contact_url', [$this, 'contactUrl']),
        ];
    }

    public function context(): array
    {
        $request = $this->requests->getCurrentRequest();
        $path = QualificationContext::path($request?->getPathInfo());
        $source = $path;
        if (in_array($path, ['/contact', '/en/contact', '/es/contacto'], true)) {
            $candidate = $request?->query->getString('source_path', '') ?? '';
            if (str_starts_with($candidate, '/') && QualificationContext::need($candidate) !== 'contact') {
                $source = QualificationContext::path($candidate);
            }
        }
        return ['need' => QualificationContext::need($source), 'sourcePath' => $source,
            'erpAllowed' => QualificationContext::allowsErp($path)];
    }

    public function contactUrl(string $url): string
    {
        $context = $this->context();
        if (!in_array(parse_url($url, PHP_URL_PATH), ['/contact', '/en/contact', '/es/contacto'], true)
            || $context['need'] === 'contact') {
            return $url;
        }
        return $url.(str_contains($url, '?') ? '&' : '?').http_build_query([
            'need' => $context['need'], 'source_path' => $context['sourcePath'],
        ]);
    }
}

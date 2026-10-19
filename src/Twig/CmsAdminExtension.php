<?php
namespace App\Twig;
use App\Service\CmsAdminPresentation;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
final class CmsAdminExtension extends AbstractExtension
{
    public function __construct(private CmsAdminPresentation $presentation) {}
    public function getFunctions(): array { return [new TwigFunction('cms_admin_item',[$this->presentation,'item'])]; }
}

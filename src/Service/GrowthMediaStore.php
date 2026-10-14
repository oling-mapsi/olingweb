<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class GrowthMediaStore
{
    public function __construct(#[Autowire('%kernel.project_dir%')] private readonly string $projectDir) {}

    public function import(array $payload): array
    {
        $bytes = base64_decode((string) ($payload['content_base64'] ?? ''), true);
        $info = $bytes !== false ? @getimagesizefromstring($bytes) : false;
        $extensions = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp'];
        if (!$info || strlen($bytes) > 12582912 || $info[0] * $info[1] > 16777216 || !isset($extensions[$info['mime']])) {
            throw new BadRequestHttpException('Invalid Growth illustration.');
        }
        $hash = hash('sha256', $bytes);
        if (!hash_equals($hash, (string) ($payload['sha256'] ?? ''))) {
            throw new BadRequestHttpException('Illustration checksum mismatch.');
        }
        $name = $hash.'.'.$extensions[$info['mime']];
        $directory = $this->projectDir.'/var/growth-media';
        if (!is_dir($directory) && !mkdir($directory, 0770, true) && !is_dir($directory)) {
            throw new \RuntimeException('Media storage unavailable.');
        }
        if (!is_file($directory.'/'.$name)) {
            $temporary = tempnam($directory, 'import-');
            if ($temporary === false || file_put_contents($temporary, $bytes, LOCK_EX) !== strlen($bytes)) {
                throw new \RuntimeException('Media import failed.');
            }
            chmod($temporary, 0660);
            rename($temporary, $directory.'/'.$name);
        }
        return ['sha256' => $hash, 'public_path' => '/uploads/growth/'.$name, 'mime_type' => $info['mime'], 'width' => $info[0], 'height' => $info[1]];
    }

    public function publishHtmlMedia(string $html): void
    {
        foreach ($this->images($html) as [$image, $name]) {
            $private = $this->projectDir.'/var/growth-media/'.$name;
            $public = $this->projectDir.'/public/uploads/growth/'.$name;
            if (!is_file($private) && !is_file($public)) {
                throw new BadRequestHttpException('Article illustration is unavailable.');
            }
            if (!is_file($public)) {
                if (!is_dir(dirname($public))) mkdir(dirname($public), 0775, true);
                if (!copy($private, $public)) throw new \RuntimeException('Media publication failed.');
            }
        }
    }

    public function privatePreviewHtml(string $html): string
    {
        $document = $this->document($html);
        foreach ($this->images($html, $document) as [$image, $name]) {
            $file = $this->projectDir.'/var/growth-media/'.$name;
            if (is_file($file)) {
                $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file);
                $image->setAttribute('src', 'data:'.$mime.';base64,'.base64_encode(file_get_contents($file)));
            }
        }
        $body = $document->getElementsByTagName('body')->item(0);
        $output = '';
        foreach ($body?->childNodes ?? [] as $node) $output .= $document->saveHTML($node);
        return $output;
    }

    private function images(string $html, ?\DOMDocument $document = null): iterable
    {
        $document ??= $this->document($html);
        foreach ($document->getElementsByTagName('img') as $image) {
            $path = parse_url($image->getAttribute('src'), PHP_URL_PATH);
            if (is_string($path) && preg_match('#^/uploads/growth/([a-f0-9]{64}\.(?:png|jpg|webp))$#', $path, $match)) {
                yield [$image, $match[1]];
            }
        }
    }

    private function document(string $html): \DOMDocument
    {
        $document = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?><html><body>'.$html.'</body></html>', LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        return $document;
    }
}

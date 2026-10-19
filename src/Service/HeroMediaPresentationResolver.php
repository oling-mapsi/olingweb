<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;

final class HeroMediaPresentationResolver
{
    private const PHOTOGRAPHS = [
        '/img/spe/practice1f.jpg',
        '/img/spe/practice2f.jpg',
        '/uploads/services/hero/img3-hero-hero-69a3a6021e1b18.67507182.jpg',
    ];

    public function __construct(
        #[Autowire('%kernel.project_dir%/public')] private readonly string $publicDir,
        #[Autowire(service: 'cache.app')] private readonly CacheInterface $cache,
        #[Autowire('%site_base_url%')] private readonly string $siteBaseUrl,
    ) {
    }

    public function resolve(?string $image, string $mode = 'auto', string $size = 'auto'): array
    {
        $explicit = in_array($mode, ['cover', 'contain', 'free'], true) ? $mode : null;
        $url = parse_url(trim((string) $image));
        if ($url === false || (isset($url['scheme']) && !in_array($url['scheme'], ['http', 'https'], true))
            || (isset($url['host']) && $url['host'] !== parse_url($this->siteBaseUrl, PHP_URL_HOST))) {
            return $this->withSize(['mode' => $explicit ?? 'contain', 'width' => null, 'height' => null], $size);
        }
        $path = '/'.ltrim(rawurldecode($url['path'] ?? ''), '/');
        $file = realpath($this->publicDir.$path);
        $roots = [realpath($this->publicDir), realpath($this->publicDir.'/uploads')];
        $allowed = $file && array_filter($roots, static fn ($root) => $root && str_starts_with($file, $root.DIRECTORY_SEPARATOR));
        if (!$allowed || !is_file($file) || !is_readable($file)) {
            return $this->withSize(['mode' => $explicit ?? 'contain', 'width' => null, 'height' => null], $size);
        }
        $key = 'hero_presentation_v3_'.hash('sha256', $file.'|'.filemtime($file).'|'.filesize($file));
        $result = $this->cache->get($key, function () use ($file, $path): array {
            $size = @getimagesize($file);
            $result = ['mode' => 'contain', 'width' => $size[0] ?? null, 'height' => $size[1] ?? null];
            if (!$size) {
                return $result;
            }
            if (in_array($size['mime'], ['image/png', 'image/webp'], true)) {
                $alpha = $this->analyzeAlpha($file, $size);
                if ($alpha && $alpha['transparent']) {
                    $result['mode'] = 'free';
                    if ($alpha['bounds']) { $result['bounds'] = $alpha['bounds']; }
                }
            } elseif ($size['mime'] === 'image/jpeg') {
                $knownPhoto = in_array($path, self::PHOTOGRAPHS, true) || preg_match('~^/(img/people|uploads/teams/photos)/~', $path);
                $exif = !$knownPhoto && function_exists('exif_read_data') ? @exif_read_data($file) : false;
                if ($knownPhoto || (is_array($exif) && !empty($exif['Make']) && !empty($exif['Model']))) {
                    $result['mode'] = 'cover';
                }
            }
            return $result;
        });
        $result['mode'] = $explicit ?? $result['mode'];
        return $this->withSize($result, $size);
    }

    private function withSize(array $result, string $size): array
    {
        $result['size'] = in_array($size, ['standard', 'large', 'xlarge'], true)
            ? $size : ($result['mode'] === 'cover' ? 'standard' : 'large');
        return $result;
    }

    private function analyzeAlpha(string $file, array $size): ?array
    {
        // Decode once per file version; large or unsupported images remain uncropped.
        if ($size[0] * $size[1] > 4000000 || filesize($file) > 10000000 || !function_exists('imagecreatefrompng')) {
            return null;
        }
        $image = $size['mime'] === 'image/png' ? @imagecreatefrompng($file)
            : (function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file) : false);
        if (!$image) {
            return null;
        }
        try {
            $trueColor = imageistruecolor($image);
            $transparent = false;
            $left = $size[0]; $top = $size[1]; $right = -1; $bottom = -1;
            for ($y = 0; $y < $size[1]; ++$y) {
                for ($x = 0; $x < $size[0]; ++$x) {
                    $color = imagecolorat($image, $x, $y);
                    $alpha = $trueColor ? (($color >> 24) & 0x7f) : imagecolorsforindex($image, $color)['alpha'];
                    $transparent = $transparent || $alpha > 0;
                    if ($alpha < 127) {
                        $left = min($left, $x); $right = max($right, $x);
                        $top = min($top, $y); $bottom = max($bottom, $y);
                    }
                }
            }
            $bounds = null;
            if ($right >= $left && (($right - $left + 1) < .9 * $size[0] || ($bottom - $top + 1) < .9 * $size[1])) {
                // Keep every nontransparent pixel, including shadows, plus a safety margin.
                $padding = max(2, (int) ceil(min($size[0], $size[1]) * .015));
                $left = max(0, $left - $padding); $top = max(0, $top - $padding);
                $right = min($size[0] - 1, $right + $padding); $bottom = min($size[1] - 1, $bottom + $padding);
                $bounds = ['x' => $left, 'y' => $top, 'width' => $right - $left + 1, 'height' => $bottom - $top + 1];
            }
            return ['transparent' => $transparent, 'bounds' => $bounds];
        } finally {
            imagedestroy($image);
        }
    }
}

<?php

namespace App\Service\Analytics;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;

class InternalAnalyticsExclusionService
{
    public const COOKIE_NAME = 'oling_internal_analytics_excluded';

    public function __construct(private readonly string $secret)
    {
    }

    public function isExcluded(Request $request): bool
    {
        $value = (string) $request->cookies->get(self::COOKIE_NAME, '');
        if ($value === '' || !str_contains($value, '.')) {
            return false;
        }

        [$flag, $signature] = explode('.', $value, 2);
        return $flag === '1' && hash_equals($this->sign($flag), $signature);
    }

    public function createCookie(): Cookie
    {
        $flag = '1';
        return Cookie::create(self::COOKIE_NAME, $flag.'.'.$this->sign($flag))
            ->withExpires(new \DateTimeImmutable('+12 months'))
            ->withPath('/')
            ->withSecure(true)
            ->withHttpOnly(true)
            ->withSameSite(Cookie::SAMESITE_LAX);
    }

    public function clearCookie(): Cookie
    {
        return Cookie::create(self::COOKIE_NAME)->withExpires(new \DateTimeImmutable('-1 year'))->withPath('/');
    }

    private function sign(string $value): string
    {
        return hash_hmac('sha256', $value, $this->secret);
    }
}

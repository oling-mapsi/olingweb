<?php

namespace App\Service\Analytics;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Ga4DataApiClient
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly HttpClientInterface $httpClient,
        private readonly ?string $propertyId,
        private readonly ?string $credentialsPath
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function status(): array
    {
        if (!$this->propertyId) {
            return [
                'configured' => false,
                'label' => 'Property GA4 à configurer',
                'lastRefresh' => null,
            ];
        }
        if (!$this->credentialsPath || !is_readable($this->credentialsPath)) {
            return [
                'configured' => false,
                'label' => 'Credentials GA4 à configurer',
                'lastRefresh' => null,
            ];
        }

        return $this->cache->get('analytics.ga4.status', function (ItemInterface $item) {
            $item->expiresAfter(900);
            return [
                'configured' => true,
                'label' => 'GA4 configuré, connecteur prêt',
                'lastRefresh' => (new \DateTimeImmutable())->format('d/m/Y H:i'),
            ];
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        if (!$this->propertyId) {
            return ['configured' => false, 'error' => 'Property GA4 à configurer'];
        }
        if (!$this->credentialsPath || !is_readable($this->credentialsPath)) {
            return ['configured' => false, 'error' => 'Credentials GA4 à configurer'];
        }

        $key = sprintf('analytics.ga4.summary.%s.%s', $from->format('Ymd'), $to->format('Ymd'));

        return $this->cache->get($key, function (ItemInterface $item) use ($from, $to) {
            $item->expiresAfter(1800);

            try {
                return [
                    'configured' => true,
                    'totals' => $this->runReport($from, $to, ['sessions', 'screenPageViews', 'engagedSessions'], []),
                    'pages' => $this->runReport($from, $to, ['screenPageViews', 'sessions'], ['pagePath']),
                    'sources' => $this->runReport($from, $to, ['sessions', 'screenPageViews'], ['sessionSource', 'sessionMedium']),
                    'daily' => $this->runReport($from, $to, ['sessions', 'screenPageViews'], ['date']),
                    'error' => null,
                ];
            } catch (\Throwable $exception) {
                return [
                    'configured' => true,
                    'error' => $exception->getMessage(),
                ];
            }
        });
    }

    /**
     * @param string[] $metrics
     * @param string[] $dimensions
     * @return array<string, mixed>
     */
    public function runReport(\DateTimeImmutable $from, \DateTimeImmutable $to, array $metrics, array $dimensions): array
    {
        $token = $this->accessToken();
        $response = $this->httpClient->request('POST', sprintf('https://analyticsdata.googleapis.com/v1beta/properties/%s:runReport', $this->propertyId), [
            'auth_bearer' => $token,
            'json' => [
                'dateRanges' => [[
                    'startDate' => $from->format('Y-m-d'),
                    'endDate' => $to->modify('-1 day')->format('Y-m-d'),
                ]],
                'metrics' => array_map(static fn (string $name): array => ['name' => $name], $metrics),
                'dimensions' => array_map(static fn (string $name): array => ['name' => $name], $dimensions),
                'limit' => 100,
            ],
        ]);

        if ($response->getStatusCode() >= 400) {
            throw new \RuntimeException('Erreur GA4 Data API '.$response->getStatusCode());
        }

        return $response->toArray(false);
    }

    private function accessToken(): string
    {
        return $this->cache->get('analytics.ga4.access_token', function (ItemInterface $item) {
            $item->expiresAfter(3300);
            $credentials = $this->credentials();
            $now = time();
            $assertion = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR))
                .'.'.$this->base64UrlEncode(json_encode([
                    'iss' => $credentials['client_email'],
                    'scope' => 'https://www.googleapis.com/auth/analytics.readonly',
                    'aud' => 'https://oauth2.googleapis.com/token',
                    'iat' => $now,
                    'exp' => $now + 3600,
                ], JSON_THROW_ON_ERROR));

            openssl_sign($assertion, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256);
            $jwt = $assertion.'.'.$this->base64UrlEncode($signature);
            $response = $this->httpClient->request('POST', 'https://oauth2.googleapis.com/token', [
                'body' => [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt,
                ],
            ]);

            if ($response->getStatusCode() >= 400) {
                throw new \RuntimeException('Authentification GA4 impossible');
            }

            $payload = $response->toArray(false);
            if (empty($payload['access_token']) || !is_string($payload['access_token'])) {
                throw new \RuntimeException('Jeton GA4 absent de la réponse OAuth');
            }

            return $payload['access_token'];
        });
    }

    /**
     * @return array{client_email: string, private_key: string}
     */
    private function credentials(): array
    {
        $raw = $this->credentialsPath ? file_get_contents($this->credentialsPath) : false;
        if ($raw === false) {
            throw new \RuntimeException('Credentials GA4 illisibles');
        }
        $decoded = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);
        if (empty($decoded['client_email']) || empty($decoded['private_key'])) {
            throw new \RuntimeException('Credentials GA4 incomplets');
        }

        return [
            'client_email' => (string) $decoded['client_email'],
            'private_key' => (string) $decoded['private_key'],
        ];
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}

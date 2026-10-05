<?php

namespace App\Service\Growth;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class MapsiProductFactsClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $baseUrl,
        private readonly string $token,
    ) {
    }

    public function health(): array { return $this->get('/api/internal/growth/v1/health'); }
    public function capabilities(): array { return $this->get('/api/internal/growth/v1/capabilities'); }
    public function contactSnapshot(): array { return $this->get('/api/internal/growth/v1/contact-snapshot'); }
    public function usageSnapshot(): array { return $this->get('/api/internal/growth/v1/usage-snapshot'); }
    public function productChanges(): array { return $this->get('/api/internal/growth/v1/product-changes'); }

    private function get(string $path): array
    {
        if (trim($this->baseUrl) === '' || trim($this->token) === '') {
            throw new \RuntimeException('MAPSI product facts client is not configured.');
        }

        $response = $this->httpClient->request('GET', rtrim($this->baseUrl, '/').$path, [
            'headers' => ['Authorization' => 'Bearer '.$this->token],
            'timeout' => 10,
        ]);

        return $response->toArray();
    }
}

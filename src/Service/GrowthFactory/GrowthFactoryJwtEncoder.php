<?php

namespace App\Service\GrowthFactory;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\User\UserInterface;

final class GrowthFactoryJwtEncoder
{
    public function __construct(
        private readonly GrowthFactoryRoleMapper $roleMapper,
        #[Autowire(param: 'growth_factory_jwt_issuer')]
        private readonly string $issuer,
        #[Autowire(param: 'growth_factory_jwt_audience')]
        private readonly string $audience,
        #[Autowire(param: 'growth_factory_jwt_ttl_seconds')]
        private readonly int $ttlSeconds,
        #[Autowire(param: 'growth_factory_jwt_kid')]
        private readonly ?string $keyId,
        #[Autowire(param: 'growth_factory_private_key')]
        private readonly ?string $privateKeyPem,
        #[Autowire(param: 'growth_factory_private_key_file')]
        private readonly ?string $privateKeyFile,
    ) {
    }

    public function encode(UserInterface $actor, string $correlationId): string
    {
        $now = time();
        $roles = $this->roleMapper->map($actor->getRoles());
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        if (trim((string) $this->keyId) !== '') {
            $header['kid'] = $this->keyId;
        }

        $payload = [
            'iss' => $this->issuer,
            'aud' => $this->audience,
            'sub' => 'oling-admin:'.$actor->getUserIdentifier(),
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $this->ttlSeconds,
            'jti' => bin2hex(random_bytes(16)),
            'roles' => $roles,
            'customer' => 'oling-internal',
            'correlation_id' => $correlationId,
            'actor' => [
                'type' => 'oling_admin',
                'email' => $actor->getUserIdentifier(),
                'display_name' => $actor->getUserIdentifier(),
                'roles' => $actor->getRoles(),
                'growth_permissions' => $roles,
            ],
        ];

        $segments = [
            $this->base64Url(json_encode($header, JSON_THROW_ON_ERROR)),
            $this->base64Url(json_encode($payload, JSON_THROW_ON_ERROR)),
        ];
        $signature = '';
        if (!openssl_sign(implode('.', $segments), $signature, $this->privateKey(), OPENSSL_ALGO_SHA256)) {
            throw new \RuntimeException('Impossible de signer le JWT Growth.');
        }
        $segments[] = $this->base64Url($signature);

        return implode('.', $segments);
    }

    private function privateKey(): \OpenSSLAsymmetricKey
    {
        $pem = trim((string) $this->privateKeyPem);
        if ($pem === '' && trim((string) $this->privateKeyFile) !== '') {
            $pem = trim((string) @file_get_contents((string) $this->privateKeyFile));
        }
        if ($pem === '') {
            throw new \RuntimeException('Clé privée Growth absente.');
        }
        $key = openssl_pkey_get_private($pem);
        if (!$key instanceof \OpenSSLAsymmetricKey) {
            throw new \RuntimeException('Clé privée Growth invalide.');
        }

        return $key;
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}

<?php

namespace App\Tests;

use App\Controller\ContactController;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ContactControllerTest extends TestCase
{
    public function testValidSubmissionPersistsAndSendsEmail(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist');
        $entityManager->expects(self::once())->method('flush');
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::once())->method('send');

        $response = $this->submit($this->validPayload(), true, $this->limiter(), $entityManager, $mailer);

        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($this->payload($response)['success']);
    }

    public function testMissingAndInvalidCsrfAreRejectedWithoutSideEffects(): void
    {
        foreach (['', 'invalid'] as $token) {
            $payload = $this->validPayload();
            $payload['_token'] = $token;
            $response = $this->submit($payload, false);

            self::assertSame(403, $response->getStatusCode());
            self::assertFalse($this->payload($response)['success']);
        }
    }

    public function testFilledHoneypotIsRejectedWithoutSideEffects(): void
    {
        $payload = $this->validPayload();
        $payload['website'] = 'https://spam.example';

        $response = $this->submit($payload);

        self::assertSame(400, $response->getStatusCode());
        self::assertFalse($this->payload($response)['success']);
    }

    public function testRateLimitRejectsBurstWithoutSecondWriteOrEmail(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist');
        $entityManager->expects(self::once())->method('flush');
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::once())->method('send');
        $limiter = $this->limiter(1);

        $first = $this->submit($this->validPayload(), true, $limiter, $entityManager, $mailer);
        $second = $this->submit($this->validPayload(), true, $limiter, $entityManager, $mailer);

        self::assertSame(200, $first->getStatusCode());
        self::assertSame(429, $second->getStatusCode());
        self::assertFalse($this->payload($second)['success']);
    }

    public function testInvalidEmailAndOversizedMessageAreRejectedWithoutSideEffects(): void
    {
        $payload = $this->validPayload();
        $payload['contactWorkEmail'] = "bad\nBcc:test@example.test";
        $payload['contactDetails'] = str_repeat('A', 201);

        $response = $this->submit($payload);

        self::assertSame(200, $response->getStatusCode());
        self::assertFalse($this->payload($response)['success']);
    }

    private function submit(
        array $payload,
        bool $validCsrf = true,
        ?RateLimiterFactory $limiter = null,
        ?EntityManagerInterface $entityManager = null,
        ?MailerInterface $mailer = null
    ) {
        $controller = new ContactController();
        $controller->setContainer(new Container());

        $csrf = $this->createMock(CsrfTokenManagerInterface::class);
        $csrf->method('isTokenValid')->willReturn($validCsrf);
        $entityManager ??= $this->createMock(EntityManagerInterface::class);
        $mailer ??= $this->createMock(MailerInterface::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $validator->method('validate')->willReturn(new ConstraintViolationList());
        $request = Request::create('/send-email', 'POST', $payload, server: ['REMOTE_ADDR' => '203.0.113.10']);

        return $controller->sendEmail(
            $request,
            $entityManager,
            $validator,
            $mailer,
            $csrf,
            $limiter ?? $this->limiter()
        );
    }

    private function limiter(int $limit = 5): RateLimiterFactory
    {
        return new RateLimiterFactory([
            'id' => 'contact_form_test',
            'policy' => 'fixed_window',
            'limit' => $limit,
            'interval' => '10 minutes',
        ], new InMemoryStorage());
    }

    private function validPayload(): array
    {
        return [
            '_token' => 'valid',
            'website' => '',
            'contactFirstName' => 'Élodie',
            'contactLastName' => "D'Aubigné",
            'contactCompany' => 'Société Exemple',
            'contactWorkEmail' => 'elodie@example.test',
            'contactDetails' => "Bonjour,\nNous souhaitons cadrer notre projet ERP.",
            'consent' => '1',
        ];
    }

    private function payload($response): array
    {
        return json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }
}

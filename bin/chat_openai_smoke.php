<?php

declare(strict_types=1);

use App\Entity\ChatConversation;
use App\Entity\ChatMessage;
use App\Kernel;
use App\Service\Chat\Ai\OpenAiResponsesProvider;

require dirname(__DIR__).'/vendor/autoload.php';

$dotenvPath = dirname(__DIR__).'/.env.local';
if (is_file($dotenvPath)) {
    foreach (file($dotenvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        if ($name === '' || getenv($name) !== false) {
            continue;
        }

        $value = trim($value);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        putenv($name.'='.$value);
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

$kernel = new Kernel('test', true);
$kernel->boot();

$container = $kernel->getContainer()->get('test.service_container');

/** @var OpenAiResponsesProvider $provider */
$provider = $container->get(OpenAiResponsesProvider::class);

$conversation = new ChatConversation();
$message = (new ChatMessage())
    ->setRole('visitor')
    ->setMessageType('answer')
    ->setContent('Faites-vous de l AMOA ERP ?')
    ->setSequenceNumber(1)
    ->setCreatedAt(new DateTimeImmutable());
$conversation->addMessage($message);

$result = [
    'available' => $provider->isAvailable(),
    'ok' => false,
    'error' => null,
];

try {
    $decision = $provider->generateDecision($conversation, $message->getContent(), [], []);
    $result['ok'] = true;
    $result['provider'] = $decision->provider;
    $result['preview'] = mb_substr($decision->reply, 0, 220);
} catch (Throwable $exception) {
    $result['error'] = $exception->getMessage();
    if ($exception->getPrevious() instanceof Throwable) {
        $result['previous'] = $exception->getPrevious()->getMessage();
    }
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL;

$kernel->shutdown();

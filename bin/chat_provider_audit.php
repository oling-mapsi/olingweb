<?php

declare(strict_types=1);

use App\Entity\ChatConversation;
use App\Entity\ChatMessage;
use App\Kernel;
use App\Service\Chat\Ai\OpenAiResponsesProvider;
use App\Service\Chat\ChatResponder;

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

/** @var \Symfony\Component\DependencyInjection\ContainerInterface $container */
$container = $kernel->getContainer()->get('test.service_container');

/** @var ChatResponder $responder */
$responder = $container->get(ChatResponder::class);
/** @var OpenAiResponsesProvider $openAiProvider */
$openAiProvider = $container->get(OpenAiResponsesProvider::class);

$questions = [
    'projet SI client et facturation avez vous des références eaux et assainissement ?',
    'vous faites amoa progiciel ?',
    'eaux et assainissement ?',
    'comment accompagnez-vous un projet ERP ?',
    'quelles solutions et progiciels maîtrisez-vous ?',
    'quel expert oling pour mon projet erp ?',
    'faites-vous des audits rgpd ?',
    'amoa iso27001',
    'notre ERP est obsolète, vous intervenez sur le cadrage ?',
    'avez-vous des références dans l industrie ?',
    'vous connaissez sage x3 ?',
    'vous faites gmao ?',
    'on cherche une aide pour reprise de données et interfaces',
    'vous intervenez sur la facturation électronique ?',
    'je veux parler à un consultant',
    'votre numéro de téléphone ?',
    'qui chez oling pour un projet crm ?',
    'avez-vous déjà travaillé avec veolia ?',
    'besoin d un dpo externe, vous faites ?',
    'projet multi sites avec SI finance et reporting, quelle approche ?',
];

$rows = [];
$openAiCount = 0;
$heuristicCount = 0;
$totalMs = 0;
$openAiProbe = [
    'available' => $openAiProvider->isAvailable(),
    'ok' => false,
    'error' => null,
];

$probeConversation = new ChatConversation();
$probeMessage = (new ChatMessage())
    ->setRole('visitor')
    ->setMessageType('answer')
    ->setContent('Faites-vous de l AMOA ERP ?')
    ->setSequenceNumber(1)
    ->setCreatedAt(new DateTimeImmutable());
$probeConversation->addMessage($probeMessage);

try {
    $probeDecision = $openAiProvider->generateDecision($probeConversation, $probeMessage->getContent(), [], []);
    $openAiProbe['ok'] = true;
    $openAiProbe['provider'] = $probeDecision->provider;
    $openAiProbe['preview'] = mb_substr($probeDecision->reply, 0, 220);
} catch (Throwable $exception) {
    $openAiProbe['error'] = $exception->getMessage();
    $previous = $exception->getPrevious();
    if ($previous instanceof Throwable) {
        $openAiProbe['previous'] = $previous->getMessage();
    }
}

foreach ($questions as $question) {
    $conversation = new ChatConversation();
    $message = (new ChatMessage())
        ->setRole('visitor')
        ->setMessageType('answer')
        ->setContent($question)
        ->setSequenceNumber(1)
        ->setCreatedAt(new DateTimeImmutable());
    $conversation->addMessage($message);

    $startedAt = microtime(true);
    $reply = $responder->reply($conversation, $question);
    $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
    $provider = $reply->provider ?? 'unknown';

    if ($provider === 'openai') {
        ++$openAiCount;
    } elseif ($provider === 'heuristic') {
        ++$heuristicCount;
    }

    $totalMs += $durationMs;

    $rows[] = [
        'question' => $question,
        'provider' => $provider,
        'duration_ms' => $durationMs,
        'message_type' => $reply->messageType,
        'sources' => $reply->sources,
        'preview' => mb_substr($reply->content, 0, 220),
    ];
}

echo json_encode([
    'timestamp' => (new DateTimeImmutable())->format(DATE_ATOM),
    'count' => count($questions),
    'openai_success_rate' => count($questions) > 0 ? round(($openAiCount / count($questions)) * 100, 1) : 0,
    'heuristic_fallback_rate' => count($questions) > 0 ? round(($heuristicCount / count($questions)) * 100, 1) : 0,
    'average_provider_latency_ms' => count($questions) > 0 ? round($totalMs / count($questions), 1) : 0,
    'openai_probe' => $openAiProbe,
    'rows' => $rows,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL;

$kernel->shutdown();

<?php

declare(strict_types=1);

if ($argc < 4) {
    fwrite(STDERR, "Usage: php scripts/gsc_search_analytics_fetch.php <property> <start_date> <end_date> [dimensions]\n");
    exit(1);
}

$token = getenv('GSC_ACCESS_TOKEN');
if (!$token) {
    fwrite(STDERR, "Missing GSC_ACCESS_TOKEN environment variable.\n");
    exit(1);
}

$property = $argv[1];
$startDate = $argv[2];
$endDate = $argv[3];
$dimensions = $argv[4] ?? 'date,query,page';

$payload = [
    'startDate' => $startDate,
    'endDate' => $endDate,
    'dimensions' => array_values(array_filter(array_map('trim', explode(',', $dimensions)))),
    'rowLimit' => 25000,
    'startRow' => 0,
];

$ch = curl_init(sprintf(
    'https://www.googleapis.com/webmasters/v3/sites/%s/searchAnalytics/query',
    rawurlencode($property)
));

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_THROW_ON_ERROR),
    CURLOPT_RETURNTRANSFER => true,
]);

$response = curl_exec($ch);
if ($response === false) {
    fwrite(STDERR, 'Curl error: ' . curl_error($ch) . "\n");
    curl_close($ch);
    exit(1);
}

$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($status >= 400) {
    fwrite(STDERR, "HTTP $status\n$response\n");
    exit(1);
}

echo $response . PHP_EOL;

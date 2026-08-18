<?php

declare(strict_types=1);

/**
 * Search Console Search Analytics fetch helper.
 *
 * Supported usage:
 *   GSC_ACCESS_TOKEN=... php scripts/gsc_search_analytics_fetch.php \
 *     --property="sc-domain:oling.fr" \
 *     --start="2026-08-03" \
 *     --end="2026-08-09" \
 *     --dimensions="date,query,page"
 *
 * Legacy positional usage remains supported:
 *   php scripts/gsc_search_analytics_fetch.php <property> <start_date> <end_date> [dimensions]
 */

function usage(): string
{
    return <<<TXT
Usage:
  php scripts/gsc_search_analytics_fetch.php --property=PROPERTY --start=YYYY-MM-DD --end=YYYY-MM-DD [options]
  php scripts/gsc_search_analytics_fetch.php <property> <start_date> <end_date> [dimensions]

Options:
  --property=VALUE        GSC property, e.g. sc-domain:oling.fr
  --start=YYYY-MM-DD      Start date
  --end=YYYY-MM-DD        End date
  --dimensions=LIST       Comma-separated dimensions. Default: date,query,page
  --row-limit=INT         API page size. Default: 25000
  --max-rows=INT          Hard cap across paginated requests. Default: unlimited
  --format=json|csv       Output format. Default: json
  --help                  Show this help

Environment:
  GSC_ACCESS_TOKEN        Required OAuth bearer token for Search Console API
TXT;
}

/**
 * @return array<string, mixed>
 */
function parseArgs(array $argv): array
{
    $defaults = [
        'property' => null,
        'start' => null,
        'end' => null,
        'dimensions' => 'date,query,page',
        'row-limit' => 25000,
        'max-rows' => null,
        'format' => 'json',
    ];

    $named = [];
    $positional = [];

    foreach (array_slice($argv, 1) as $arg) {
        if ($arg === '--help' || $arg === '-h') {
            $named['help'] = true;
            continue;
        }

        if (str_starts_with($arg, '--')) {
            $pair = substr($arg, 2);
            $parts = explode('=', $pair, 2);
            $key = $parts[0];
            $value = $parts[1] ?? true;
            $named[$key] = $value;
            continue;
        }

        $positional[] = $arg;
    }

    if (!empty($positional)) {
        $named['property'] ??= $positional[0] ?? null;
        $named['start'] ??= $positional[1] ?? null;
        $named['end'] ??= $positional[2] ?? null;
        $named['dimensions'] ??= $positional[3] ?? $defaults['dimensions'];
    }

    return array_merge($defaults, $named);
}

/**
 * @param array<string, mixed> $options
 */
function validateOptions(array $options): void
{
    if (!empty($options['help'])) {
        fwrite(STDOUT, usage() . PHP_EOL);
        exit(0);
    }

    foreach (['property', 'start', 'end'] as $required) {
        if (!is_string($options[$required]) || trim($options[$required]) === '') {
            fwrite(STDERR, usage() . PHP_EOL);
            fwrite(STDERR, sprintf("Missing required option --%s\n", $required));
            exit(1);
        }
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $options['start'])) {
        fwrite(STDERR, "Invalid --start date format. Expected YYYY-MM-DD.\n");
        exit(1);
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $options['end'])) {
        fwrite(STDERR, "Invalid --end date format. Expected YYYY-MM-DD.\n");
        exit(1);
    }

    if (!in_array($options['format'], ['json', 'csv'], true)) {
        fwrite(STDERR, "Invalid --format. Expected json or csv.\n");
        exit(1);
    }

    if (!is_numeric((string) $options['row-limit']) || (int) $options['row-limit'] <= 0) {
        fwrite(STDERR, "Invalid --row-limit. Expected a positive integer.\n");
        exit(1);
    }

    if ($options['max-rows'] !== null && (!is_numeric((string) $options['max-rows']) || (int) $options['max-rows'] <= 0)) {
        fwrite(STDERR, "Invalid --max-rows. Expected a positive integer.\n");
        exit(1);
    }
}

/**
 * @param array<string, mixed> $options
 * @return array{property:string,start:string,end:string,dimensions:string,rowLimit:int,maxRows:?int,format:string}
 */
function normalizeOptions(array $options): array
{
    return [
        'property' => trim((string) $options['property']),
        'start' => trim((string) $options['start']),
        'end' => trim((string) $options['end']),
        'dimensions' => trim((string) $options['dimensions']),
        'rowLimit' => (int) $options['row-limit'],
        'maxRows' => $options['max-rows'] !== null ? (int) $options['max-rows'] : null,
        'format' => (string) $options['format'],
    ];
}

/**
 * @param list<string> $dimensions
 * @return array<int, array<string, mixed>>
 */
function fetchRows(string $token, string $property, string $startDate, string $endDate, array $dimensions, int $rowLimit, ?int $maxRows): array
{
    $rows = [];
    $startRow = 0;
    $url = sprintf(
        'https://www.googleapis.com/webmasters/v3/sites/%s/searchAnalytics/query',
        rawurlencode($property)
    );

    do {
        $payload = [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'dimensions' => $dimensions,
            'rowLimit' => $rowLimit,
            'startRow' => $startRow,
        ];

        $ch = curl_init($url);
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
            $error = curl_error($ch);
            curl_close($ch);
            fwrite(STDERR, 'Curl error: ' . $error . PHP_EOL);
            exit(1);
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status >= 400) {
            $decoded = json_decode($response, true);
            $message = $decoded['error']['message'] ?? $response;
            fwrite(STDERR, sprintf("HTTP %d\n%s\n", $status, (string) $message));

            if ($status === 401) {
                fwrite(STDERR, "The GSC access token is missing, expired, or invalid.\n");
            } elseif ($status === 403) {
                fwrite(STDERR, "The GSC property may be incorrect or not authorized for this token.\n");
            }

            exit(1);
        }

        /** @var array{rows?: array<int, array<string,mixed>>} $decoded */
        $decoded = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        $pageRows = $decoded['rows'] ?? [];

        foreach ($pageRows as $row) {
            $rows[] = $row;
            if ($maxRows !== null && count($rows) >= $maxRows) {
                return $rows;
            }
        }

        $startRow += count($pageRows);
    } while (count($pageRows) === $rowLimit);

    return $rows;
}

/**
 * @param list<string> $dimensions
 * @param array<int, array<string, mixed>> $rows
 */
function outputCsv(array $dimensions, array $rows): void
{
    $handle = fopen('php://output', 'wb');
    if ($handle === false) {
        fwrite(STDERR, "Unable to write CSV output.\n");
        exit(1);
    }

    fputcsv($handle, array_merge($dimensions, ['clicks', 'impressions', 'ctr', 'position']));

    foreach ($rows as $row) {
        $keys = $row['keys'] ?? [];
        $line = [];
        foreach ($dimensions as $index => $dimension) {
            $line[] = $keys[$index] ?? '';
        }
        $line[] = $row['clicks'] ?? '';
        $line[] = $row['impressions'] ?? '';
        $line[] = $row['ctr'] ?? '';
        $line[] = $row['position'] ?? '';
        fputcsv($handle, $line);
    }

    fclose($handle);
}

$parsed = parseArgs($argv);
validateOptions($parsed);
$options = normalizeOptions($parsed);

$token = getenv('GSC_ACCESS_TOKEN');
if (!$token) {
    fwrite(STDERR, "Missing GSC_ACCESS_TOKEN environment variable.\n");
    exit(1);
}

$dimensions = array_values(array_filter(array_map('trim', explode(',', $options['dimensions']))));
if ($dimensions === []) {
    fwrite(STDERR, "At least one dimension is required.\n");
    exit(1);
}

$rows = fetchRows(
    $token,
    $options['property'],
    $options['start'],
    $options['end'],
    $dimensions,
    $options['rowLimit'],
    $options['maxRows']
);

if ($options['format'] === 'csv') {
    outputCsv($dimensions, $rows);
    exit(0);
}

echo json_encode([
    'property' => $options['property'],
    'startDate' => $options['start'],
    'endDate' => $options['end'],
    'dimensions' => $dimensions,
    'rowCount' => count($rows),
    'rows' => $rows,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;

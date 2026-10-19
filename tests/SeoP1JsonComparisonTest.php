<?php

namespace App\Tests;

use DoctrineMigrations\Version20261019220000 as Migration;
use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../migrations/Version20261019220000.php';

final class SeoP1JsonComparisonTest extends TestCase
{
    /** @dataProvider jsonCases */
    public function testJsonStructureComparison(mixed $current, mixed $expected, bool $equal): void
    {
        self::assertSame($equal, Migration::valuesAreEquivalent('json', $current, $expected));
    }

    public static function jsonCases(): iterable
    {
        yield 'object key order' => [['a' => 1, 'b' => 2], ['b' => 2, 'a' => 1], true];
        yield 'nested object key order' => [
            ['landingNarrative' => ['title' => 'x', 'citablePassages' => [['title' => 'A', 'text' => 'B']]]],
            ['landingNarrative' => ['citablePassages' => [['text' => 'B', 'title' => 'A']], 'title' => 'x']], true,
        ];
        yield 'list order' => [['a', 'b'], ['b', 'a'], false];
        yield 'integer versus string' => [['value' => 1], ['value' => '1'], false];
        yield 'boolean versus integer' => [['value' => true], ['value' => 1], false];
        yield 'null versus empty string' => [['value' => null], ['value' => ''], false];
        yield 'integer versus float' => [['value' => 1], ['value' => 1.0], false];
        yield 'missing versus null' => [[], ['value' => null], false];
        yield 'nested missing versus null' => [['a' => []], ['a' => ['value' => null]], false];
        yield 'nested ordered list' => [['a' => [['x' => 1], ['x' => 2]]], ['a' => [['x' => 2], ['x' => 1]]], false];
    }

    public function testOrdinarySqlValuesKeepStrictComparison(): void
    {
        self::assertFalse(Migration::valuesAreEquivalent('scalar', 1, '1'));
        self::assertFalse(Migration::valuesAreEquivalent('scalar', true, 1));
        self::assertFalse(Migration::valuesAreEquivalent('scalar', null, ''));
        self::assertFalse(Migration::valuesAreEquivalent('scalar', '{"a":1,"b":2}', '{"b":2,"a":1}'));
        self::assertTrue(Migration::valuesAreEquivalent('scalar', 'Same text', 'Same text'));
    }
}

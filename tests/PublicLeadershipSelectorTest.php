<?php

declare(strict_types=1);

namespace App\Tests;

use App\Service\PublicLeadershipSelector;
use PHPUnit\Framework\TestCase;

final class PublicLeadershipSelectorTest extends TestCase
{
    public function testItSelectsOnlyTheTwoEditorialProfilesInStableOrder(): void
    {
        $profiles = [
            ['slug' => 'consultant-extra', 'noncomplet' => 'Published contributor'],
            ['slug' => 'dorothee-maitrias', 'noncomplet' => 'Dorothée Maitrias'],
            ['slug' => 'florestan-rouet', 'noncomplet' => 'Florestan Rouet'],
            ['slug' => 'florestan-rouet', 'noncomplet' => 'Duplicate'],
            ['slug' => 'another-contributor', 'noncomplet' => 'Another contributor'],
        ];

        $selected = (new PublicLeadershipSelector())->select($profiles);

        self::assertSame(['florestan-rouet', 'dorothee-maitrias'], array_column($selected, 'slug'));
        self::assertSame('Florestan Rouet', $selected[0]['noncomplet']);
        self::assertSame('Dorothée Maitrias', $selected[1]['noncomplet']);
        self::assertCount(5, $profiles, 'The source profiles must remain untouched.');
    }

    public function testItDoesNotInventProfilesMissingFromLocalizedPublication(): void
    {
        $selector = new PublicLeadershipSelector();

        self::assertSame([], $selector->select([]));
        self::assertSame(
            [['slug' => 'florestan-rouet', 'noncomplet' => 'Published profile']],
            $selector->select([
                ['slug' => 'unpublished-other', 'noncomplet' => 'Not selected'],
                ['slug' => 'florestan-rouet', 'noncomplet' => 'Published profile'],
            ])
        );
    }

    public function testMalformedSlugsCannotPassTheEditorialFilter(): void
    {
        $selected = (new PublicLeadershipSelector())->select([
            ['slug' => null],
            ['slug' => 42],
            ['slug' => 'Florestan Rouet'],
            ['noncomplet' => 'Missing slug'],
        ]);

        self::assertSame([], $selected);
    }
}

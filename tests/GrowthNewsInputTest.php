<?php

namespace App\Tests;

use App\Dto\GrowthNewsInput;
use PHPUnit\Framework\TestCase;

class GrowthNewsInputTest extends TestCase
{
    public function testPayloadNormalization(): void
    {
        $input = GrowthNewsInput::fromArray([
            'external_id' => ' ext-123 ',
            'title' => 'Titre',
            'slug' => 'slug-test',
            'excerpt' => 'Resume',
            'content_html' => '<p>Contenu</p>',
            'meta_title' => 'Meta',
            'meta_description' => 'Description',
            'categories' => [' A ', '', 'A', 'B'],
            'tags' => ['tag-1', 'tag-1', 'tag-2'],
            'publication_date' => '2026-07-11T09:00:00+00:00',
            'status' => 'draft',
            'author_display_name' => 'Auteur',
            'source_campaign_id' => 'cmp-9',
        ]);

        self::assertSame('ext-123', $input->externalId);
        self::assertSame(['A', 'B'], $input->categories);
        self::assertSame(['tag-1', 'tag-2'], $input->tags);
        self::assertInstanceOf(\DateTimeImmutable::class, $input->publicationDate);
    }
}

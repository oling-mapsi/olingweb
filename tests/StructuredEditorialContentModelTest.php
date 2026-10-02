<?php

namespace App\Tests;

use App\Entity\FaqItem;
use App\Entity\FaqItemTranslation;
use App\Entity\PageBlock;
use App\Entity\PageBlockTranslation;
use App\Entity\PageCta;
use App\Entity\PageCtaTranslation;
use App\Entity\RelatedLink;
use App\Entity\RelatedLinkTranslation;
use App\Entity\SiteGlobalContent;
use App\Entity\SiteGlobalContentTranslation;
use App\Entity\SitePageTranslation;
use PHPUnit\Framework\TestCase;

class StructuredEditorialContentModelTest extends TestCase
{
    public function testPageBlockKeepsEditorialCopyInTranslation(): void
    {
        $block = (new PageBlock())
            ->setBlockType(PageBlock::TYPE_FEATURE_LIST)
            ->setSortOrder(20)
            ->setIcon('shield');

        $translation = (new PageBlockTranslation())
            ->setPageBlock($block)
            ->setLocale(SitePageTranslation::LOCALE_FR)
            ->setTitle('DATABASE VALUE')
            ->setBodyHtml('<p>Texte FR en base.</p>')
            ->setTranslationStatus(SitePageTranslation::STATUS_PUBLISHED);

        self::assertSame(PageBlock::TYPE_FEATURE_LIST, $block->getBlockType());
        self::assertSame('DATABASE VALUE', $translation->getTitle());
        self::assertSame('<p>Texte FR en base.</p>', $translation->getBodyHtml());
    }

    public function testFaqCtaRelatedLinkAndGlobalContentUseTranslations(): void
    {
        $faqTranslation = (new FaqItemTranslation())
            ->setFaqItem((new FaqItem())->setSortOrder(1))
            ->setQuestion('DATABASE QUESTION')
            ->setAnswerHtml('<p>DATABASE ANSWER</p>');

        $ctaTranslation = (new PageCtaTranslation())
            ->setPageCta((new PageCta())->setPlacement('final')->setUrl('/contact'))
            ->setLabel('DATABASE CTA')
            ->setTitle('DATABASE CTA TITLE');

        $linkTranslation = (new RelatedLinkTranslation())
            ->setRelatedLink((new RelatedLink())->setSortOrder(2))
            ->setLabel('DATABASE LINK')
            ->setDescription('DATABASE LINK DESCRIPTION');

        $globalTranslation = (new SiteGlobalContentTranslation())
            ->setSiteGlobalContent((new SiteGlobalContent())->setIdentifier('footer'))
            ->setTitle('DATABASE GLOBAL')
            ->setBodyHtml('<p>DATABASE GLOBAL BODY</p>');

        self::assertSame('DATABASE QUESTION', $faqTranslation->getQuestion());
        self::assertSame('DATABASE CTA', $ctaTranslation->getLabel());
        self::assertSame('DATABASE LINK', $linkTranslation->getLabel());
        self::assertSame('DATABASE GLOBAL', $globalTranslation->getTitle());
    }
}

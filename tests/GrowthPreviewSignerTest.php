<?php

namespace App\Tests;

use App\Service\GrowthPreviewSigner;
use PHPUnit\Framework\TestCase;

class GrowthPreviewSignerTest extends TestCase
{
    public function testSignatureValidation(): void
    {
        $signer = new GrowthPreviewSigner('preview-secret', 600);
        $expires = time() + 600;
        $signature = $signer->buildSignature('ext-1', 42, $expires);

        self::assertTrue($signer->isValid('ext-1', 42, $expires, $signature));
        self::assertFalse($signer->isValid('ext-1', 42, $expires, 'bad-signature'));
    }
}

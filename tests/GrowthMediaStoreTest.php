<?php

namespace App\Tests;

use App\Service\GrowthMediaStore;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class GrowthMediaStoreTest extends TestCase
{
    public function testImportedImageStaysPrivateUntilExplicitPublication(): void
    {
        $root = sys_get_temp_dir().'/growth-media-test-'.bin2hex(random_bytes(8));
        $store = new GrowthMediaStore($root);
        $bytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aHfoAAAAASUVORK5CYII=');
        $input = ['sha256' => hash('sha256', $bytes), 'content_base64' => base64_encode($bytes)];
        try {
            $media = $store->import($input);
            self::assertSame($media, $store->import($input));
            self::assertFileDoesNotExist($root.'/public'.$media['public_path']);
            $html = '<figure><img src="https://recette.invalid'.$media['public_path'].'" alt="ERP"></figure>';
            self::assertStringContainsString('data:image/png;base64,', $store->privatePreviewHtml($html));
            $store->publishHtmlMedia($html);
            self::assertSame($input['sha256'], hash_file('sha256', $root.'/public'.$media['public_path']));
        } finally {
            foreach ([$root.'/public'.$media['public_path'], $root.'/var/growth-media/'.basename($media['public_path'])] as $file) {
                if (is_file($file)) unlink($file);
            }
            foreach (['/public/uploads/growth','/public/uploads','/public','/var/growth-media','/var',''] as $directory) {
                if (is_dir($root.$directory)) rmdir($root.$directory);
            }
        }
    }

    public function testNonImagePayloadIsRejected(): void
    {
        $this->expectException(BadRequestHttpException::class);
        (new GrowthMediaStore(sys_get_temp_dir()))->import(['sha256' => hash('sha256', '<script>'), 'content_base64' => base64_encode('<script>')]);
    }
}


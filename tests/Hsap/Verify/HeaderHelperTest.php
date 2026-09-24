<?php

declare(strict_types=1);

namespace Filefabrik\HsapTest\Verify;

use Filefabrik\Hsap\Verify\HeaderHelper;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Filefabrik\Hsap\Verify\HeaderHelper::class)]
final class HeaderHelperTest extends TestCase
{
    public function testGetSignatureLine(): void
    {
        $r = new Request(method: 'Post', uri: '', headers: ['signature' => 'my-signature']);
        $this->assertSame(
            'my-signature',
            new HeaderHelper($r)->getSignatureLine(),
        );
    }

    public function testGetSignatureInputLine(): void
    {
        $r = new Request(method: 'Post', uri: '', headers: ['signature-input' => 'my-signature-input']);
        $this->assertSame(
            'my-signature-input',
            new HeaderHelper($r)->getSignatureInputLine(),
        );
    }

    public function testGetContentDigestLine(): void
    {
        $r = new Request(method: 'Post', uri: '', headers: ['content-digest' => 'my-content-digest']);
        $this->assertSame(
            'my-content-digest',
            new HeaderHelper($r)->getContentDigestLine(),
        );
    }
}

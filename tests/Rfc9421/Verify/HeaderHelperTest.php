<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Verify;

use Filefabrik\Rfc9421\Verify\HeaderHelper;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HeaderHelper::class)]
final class HeaderHelperTest extends TestCase
{
    public function testGetSignatureLine(): void
    {
        $r = new Request(method: 'Post', uri: '', headers: ['signature' => 'my-signature']);
        self::assertSame(
            'my-signature',
            new HeaderHelper($r)->getSignatureLine(),
        );
    }

    public function testGetSignatureInputLine(): void
    {
        $r = new Request(method: 'Post', uri: '', headers: ['signature-input' => 'my-signature-input']);
        self::assertSame(
            'my-signature-input',
            new HeaderHelper($r)->getSignatureInputLine(),
        );
    }

    public function testGetContentDigestLine(): void
    {
        $r = new Request(method: 'Post', uri: '', headers: ['content-digest' => 'my-content-digest']);
        self::assertSame(
            'my-content-digest',
            new HeaderHelper($r)->getContentDigestLine(),
        );
    }

    public function testHasSignatureInputLine(): void
    {
        $r = new Request(method: 'Post', uri: '', headers: ['signature-input' => 'my-signature-inputline']);
        self::assertTrue(
            new HeaderHelper($r)->hasSignatureInputLine(),
        );
        $r = new Request(method: 'Post', uri: '', headers: ['signature-inputz' => 'my-signature-inputline']);
        self::assertFalse(
            new HeaderHelper($r)->hasSignatureInputLine(),
        );
    }

    public function testHasContentDigestLine(): void
    {
        $r = new Request(method: 'Post', uri: '', headers: ['content-digest' => 'my-content-digest']);
        self::assertTrue(
            new HeaderHelper($r)->hasContentDigestLine(),
        );
        $r = new Request(method: 'Post', uri: '', headers: ['content-digestasdf' => 'my-content-digest']);
        self::assertFalse(
            new HeaderHelper($r)->hasContentDigestLine(),
        );
    }

    public function testHasSignatureLine(): void
    {
        $r = new Request(method: 'Post', uri: '', headers: ['signature' => 'my-signature']);
        self::assertTrue(
            new HeaderHelper($r)->hasSignatureLine(),
        );
        $r = new Request(method: 'Post', uri: '', headers: []);
        self::assertFalse(
            new HeaderHelper($r)->hasSignatureLine(),
        );
    }
}

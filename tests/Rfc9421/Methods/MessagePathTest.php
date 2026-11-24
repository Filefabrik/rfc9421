<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Methods;

use Filefabrik\Rfc9421\Methods\MessagePath;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MessagePath::class)]
final class MessagePathTest extends TestCase
{
    public function testHandle(): void
    {
        self::assertSame(
            '/',
            MessagePath::handle(new Request('GET', 'https://example.com')),
        );
        self::assertSame(
            '/',
            MessagePath::handle(new Request('GET', 'https://example.com/')),
        );
        self::assertSame(
            '/testing/something',
            MessagePath::handle(new Request('GET', 'https://example.com/testing/something')),
        );
        self::assertSame(
            '/testing/something/',
            MessagePath::handle(new Request('GET', 'https://example.com/testing/something/')),
        );

    }

    /**
     * @see https://url.spec.whatwg.org/#example-5434421b
     */
    public function testRfc()
    {
        self::assertSame(
            '/%F0%9F%92%A9',
            MessagePath::handle(new Request('GET', 'https://example.org/💩')),
        );

        self::assertSame(
            '/%F0%9F%8D%A3%F0%9F%8D%BA',
            MessagePath::handle(new Request('GET', 'https://url.spec.whatwg.org/🍣🍺')),
        );
    }
}

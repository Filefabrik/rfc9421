<?php

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
            '/testing/something',
            MessagePath::handle(new Request('GET', 'https://example.com/testing/something')),
        );
        self::assertSame(
            '/testing/something/',
            MessagePath::handle(new Request('GET', 'https://example.com/testing/something/')),
        );
    }
}

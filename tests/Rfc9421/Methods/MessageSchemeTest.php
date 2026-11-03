<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Methods;

use Filefabrik\Rfc9421\Exceptions\MethodException;
use Filefabrik\Rfc9421\Methods\MessageScheme;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MessageScheme::class)]
final class MessageSchemeTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     */
    public function testHandle(): void
    {
        /** @noinspection HttpUrlsUsage */
        self::assertSame('http', MessageScheme::handle(new Request('GET', 'http://example.com/')));
        self::assertSame('https', MessageScheme::handle(new Request('GET', 'https://example.com/')));
        self::assertSame('httpsdd', MessageScheme::handle(new Request('GET', 'HTTPSDD://example.com/')));
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     */
    public function testHandleThrowsResponse(): void
    {
        $this->expectExceptionObject(
            new MethodException('Missing method "getUri" for MessageInterface "GuzzleHttp\Psr7\Response'),
        );
        self::assertSame('http', MessageScheme::handle(new Response()));
    }

}

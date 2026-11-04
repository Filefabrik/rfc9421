<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Support;

use Filefabrik\Rfc9421\Exceptions\MethodException;
use Filefabrik\Rfc9421\Support\MessageInterfaceHelper;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MessageInterfaceHelper::class)]
final class MessageInterfaceHelperTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     */
    public function testHasGetUri(): void
    {
        /** @noinspection HttpUrlsUsage */
        $request = new Request('POST', 'http://example.com/pl?some=task');
        /** @noinspection HttpUrlsUsage */
        self::assertSame('http://example.com/pl?some=task', (string)MessageInterfaceHelper::hasGetUri($request));
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     */
    public function testHasGetUriThrow(): void
    {
        $this->expectExceptionObject(
            new MethodException('Missing method "getUri" for MessageInterface "GuzzleHttp\Psr7\Response"'),
        );
        MessageInterfaceHelper::hasGetUri(new Response());
    }
}

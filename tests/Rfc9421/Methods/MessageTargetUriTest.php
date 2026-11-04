<?php
/*
 * Copyright (c) 2026.  rt@filefabrik.com
 * proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Methods;

use Filefabrik\Rfc9421\Methods\MessageTargetUri;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MessageTargetUri::class)]
final class MessageTargetUriTest extends TestCase
{
    public function testHandle(): void
    {
        // todo check trailing slash
        $res = MessageTargetUri::handle(new Request('GET', 'https://test.com/'));
        self::assertSame('https://test.com/', $res);

        $res = MessageTargetUri::handle(new Request('GET', 'https://test.com'));
        self::assertSame('https://test.com', $res);

        $res = MessageTargetUri::handle(new Request('GET', 'https://test.com/var/pathy/?qer=var'));
        self::assertSame('https://test.com/var/pathy/?qer=var', $res);
    }
}

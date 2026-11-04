<?php
/*
 * Copyright (c) 2026.  rt@filefabrik.com
 * proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Methods;

use Filefabrik\Rfc9421\Methods\MessageAuthority;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MessageAuthority::class)]
final class MessageAuthorityTest extends TestCase
{
    public function testHandle(): void
    {
        $request = new Request('POST', 'https://example.com/message');
        $res     = MessageAuthority::handle($request);
        self::assertSame('example.com', $res);
    }
}

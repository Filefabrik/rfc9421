<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Methods;

use Filefabrik\Rfc9421\Methods\MesssageRequestTarget;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MesssageRequestTarget::class)]
final class MesssageRequestTargetTest extends TestCase
{
    public function testHandle(): void
    {
        $res = MesssageRequestTarget::handle(new Request('GET', 'https://test.com//resource?bat&baz=3'));
        self::assertSame('/resource?bat&baz=3', $res);
    }
}

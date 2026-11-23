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

use Filefabrik\Rfc9421\Methods\MessageQuery;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MessageQuery::class)]
final class MessageQueryTest extends TestCase
{
    public function testHandle(): void
    {
        self::assertSame(
            '?',
            MessageQuery::handle(new Request('GET', 'https://example.com')),
        );

        self::assertSame(
            '?testing=green',
            MessageQuery::handle(new Request('GET', 'https://example.com/testing/something?testing=green')),
        );
    }

    /**
     * @see https://url.spec.whatwg.org/#example-5434421b
     * @see https://url.spec.whatwg.org/#example-constructing-urlsearchparams
     */
    public function testRFC()
    {
        $request = new Request('POST', 'https://example.org/?a=b ~');

        $parsed = MessageQuery::handle($request);
        self::assertSame('?a=b%20~', $parsed);

        self::assertSame('?a=~&b=%7E', MessageQuery::handle(new Request('POST', 'https://example.com/?a=~&b=%7E')));
    }
}

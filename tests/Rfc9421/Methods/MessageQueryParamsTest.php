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

use Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException;
use Filefabrik\Rfc9421\Methods\MessageQueryParams;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MessageQueryParams::class)]
final class MessageQueryParamsTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     */
    public function testHandle(): void
    {
        $request     = new Request('GET', 'http://localhost:8001/mastodon?param=value&foo=bar&baz=bat%2Dman');
        $returnEntry = MessageQueryParams::handle($request, 'baz');

        $this->assertSame('"_baz_": "bat-man"', $returnEntry->parse());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     */
    public function testHandleEmpty(): void
    {
        $this->expectExceptionObject(new UnProcessableSignatureException('Query string named parameter not set'));
        $request = new Request('GET', 'http://localhost:8001/mastodon');
        MessageQueryParams::handle($request, 'baz');
    }
}

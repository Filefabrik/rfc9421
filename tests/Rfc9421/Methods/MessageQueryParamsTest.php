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

use Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException;
use Filefabrik\Rfc9421\Methods\MessageQueryParams;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MessageQueryParams::class)]
final class MessageQueryParamsTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     */
    public function testHandle(): void
    {
        $request     = new Request('GET', 'http://localhost:8001/mastodon?param=value&foo=bar&baz=bat%2Dman');
        $returnEntry = MessageQueryParams::handle($request, 'baz');

        self::assertSame('"@query-param";name="baz": bat-man', $returnEntry->parse());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     */
    public function testHandleQux(): void
    {
        $request     = new Request('GET', 'http://localhost:8001/path?param=value&foo=bar&baz=batman&qux=');
        $returnEntry = MessageQueryParams::handle($request, 'qux');

        self::assertSame('"@query-param";name="qux": ', $returnEntry->parse());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     */
    public function testMultiline(): void
    {
        $request     = new Request(
            'GET',
            'http://localhost:8001/parameters?var=this%20is%20a%20big%0Amultiline%20value&\
  bar=with+plus+whitespace&fa%C3%A7ade%22%3A%20=something',
        );
        $returnEntry = MessageQueryParams::handle($request, 'var');

        self::assertSame('"@query-param";name="var": this%20is%20a%20big%0Amultiline%20value', $returnEntry->parse());
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

    /**
     * todo intensive query params handler
     *
     */
    public function testParseQueryString(): void
    {
        self::assertTrue(true);
        $s = 'var=this%20is%20a%20big%0Amultiline%20value&\
  bar=with+plus+whitespace&fa%C3%A7ade%22%3A%20=something';

        $parsed = MessageQueryParams::parseQueryString($s);
    }
}

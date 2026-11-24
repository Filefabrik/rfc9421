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

use Filefabrik\Rfc9421\Exceptions\QueryParamsException;
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
     * @throws \Filefabrik\Rfc9421\Exceptions\QueryParamsException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testHandle(): void
    {
        $request     = new Request('GET', 'http://localhost:8001/mastodon?param=value&foo=bar&baz=bat%2Dman');
        $returnEntry = MessageQueryParams::handle($request, 'baz');

        self::assertSame('"@query-param";name="baz": bat-man', $returnEntry->parseComponentNameAndValue());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\QueryParamsException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testHandleQux(): void
    {
        $request     = new Request('GET', 'http://localhost:8001/path?param=value&foo=bar&baz=batman&qux=');
        $returnEntry = MessageQueryParams::handle($request, 'qux');

        self::assertSame('"@query-param";name="qux": ', $returnEntry->parseComponentNameAndValue());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\QueryParamsException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testHandleQuxNotEmpty(): void
    {
        $request     = new Request('GET', 'http://localhost:8001/path?param=value&foo=bar&baz=batman&qux=not-empty');
        $returnEntry = MessageQueryParams::handle($request, 'qux');

        self::assertSame('"@query-param";name="qux": not-empty', $returnEntry->parseComponentNameAndValue());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\QueryParamsException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testMultiline(): void
    {
        $request     = new Request(
            'GET',
            'http://localhost:8001/parameters?var=this%20is%20a%20big%0Amultiline%20value&\
  bar=with+plus+whitespace&fa%C3%A7ade%22%3A%20=something',
        );
        $returnEntry = MessageQueryParams::handle($request, 'var');

        self::assertSame(
            '"@query-param";name="var": this%20is%20a%20big%0Amultiline%20value',
            $returnEntry->parseComponentNameAndValue(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\QueryParamsException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testHandleEmpty(): void
    {
        /*
         * Todo check if the @query-params string is need during the setHeader but not exists
         */
        $this->expectExceptionObject(QueryParamsException::emptyQueryString());
        $request = new Request('GET', 'http://localhost:8001/mastodon');
        MessageQueryParams::handle($request, 'baz');
    }

    /**
     * todo intensive query params handler
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\QueryParamsException
     */
    public function testParseQueryString(): void
    {
        $this->expectExceptionObject(
            QueryParamsException::keyException(
                '\
  bar',
            ),
        );
        $s = 'var=this%20is%20a%20big%0Amultiline%20value&\
  bar=with+plus+whitespace&fa%C3%A7ade%22%3A%20=something';

        MessageQueryParams::parseQueryString($s);
    }

    /**
     * todo intensive query params handler
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\QueryParamsException
     */
    public function testParseQueryStringFalsy(): void
    {
        $s = 'fa%C3%A7ade%22%3A%20=something';

        self::assertSame(['fa%C3%A7ade%22%3A%20' => 'something'], MessageQueryParams::parseQueryString($s));
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\QueryParamsException
     */
    public function testParseQueryStringEmptyValue(): void
    {
        $s = 'var=';

        $parsed = MessageQueryParams::parseQueryString($s);
        self::assertSame(['var' => ''], $parsed);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\QueryParamsException
     */
    public function testParseQueryStringDoubleEqualSign(): void
    {
        $s = 'var=bla=test';

        $parsed = MessageQueryParams::parseQueryString($s);
        self::assertSame(['var' => 'bla=test'], $parsed);
    }

}

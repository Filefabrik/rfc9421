<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Exceptions;

use Filefabrik\Rfc9421\Exceptions\QueryParamsException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(QueryParamsException::class)]
final class QueryParamsExceptionTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\QueryParamsException
     */
    public function testValueException(): void
    {
        $this->expectExceptionObject(
            new QueryParamsException(
                'Decoding and Encoding of "//buggy value" does not match with the given value. So something is wrong with the QueryParams value',
            ),
        );
        throw QueryParamsException::valueException('//buggy value');
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\QueryParamsException
     */
    public function testKeyException(): void
    {
        $this->expectExceptionObject(
            new QueryParamsException(
                'Decoding and Encoding of "//buggy key" does not match with the given key. So something is wrong with the QueryParams key',
            ),
        );
        throw QueryParamsException::keyException('//buggy key');
    }
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\QueryParamsException
     */
    public function testEmptyQueryString(): void
    {
        $this->expectExceptionObject(
            new QueryParamsException(
                'Query string is empty',
            ),
        );
        throw QueryParamsException::emptyQueryString();
    }

}

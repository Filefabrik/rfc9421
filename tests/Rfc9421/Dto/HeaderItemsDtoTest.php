<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Dto;

use Filefabrik\Rfc9421\Dto\HeaderItemsDto;
use Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HeaderItemsDto::class)]
final class HeaderItemsDtoTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     */
    public function testFromHeaderItems(): void
    {
        $res = HeaderItemsDto::fromMessageInterface(
            new Request(
                'post',
                'localhost',
                [
                    'Host'          => 'localhost:8001',
                    'x-oriGin-host' => 'mytest.com',
                    'Example-Dict'  => 'a=1,    b=2;x=1;y=2,   c=(a   b   c), d ',
                ],
            ),
        );

        self::assertSame(
            'a=1,    b=2;x=1;y=2,   c=(a   b   c), d',
            $res->getTrimmedHeaderValue('Example-Dict'),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     */
    public function testFromArray(): void
    {
        $res = HeaderItemsDto::fromArray(
            [
                'Host'          => 'localhost:8001',
                'x-oriGin-host' => 'mytest.com',
                'Example-Dict'  => 'a=1,    b=2;x=1;y=2,   c=(a   b   c), d ',
            ],
        );

        self::assertSame(
            'a=1,    b=2;x=1;y=2,   c=(a   b   c), d',
            $res->getTrimmedHeaderValue('Example-Dict'),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     */
    public function testFromMessageInterface(): void
    {
        $res = HeaderItemsDto::fromMessageInterface(
            new Request(
                'post',
                'localhost',
                [
                    'Host'          => 'localhost:8001',
                    'x-oriGin-host' => 'mytest.com',
                    'Example-Dict'  => 'a=1,    b=2;x=1;y=2,   c=(a   b   c), d ',
                ],
            ),
        );

        self::assertSame(
            'a=1,    b=2;x=1;y=2,   c=(a   b   c), d',
            $res->getTrimmedHeaderValue('Example-Dict'),
        );
    }

    public function testHasHeaderKey(): void
    {
        $res = HeaderItemsDto::fromMessageInterface(
            new Request(
                'post',
                'localhost',
                [
                    'Host'          => 'localhost:8001',
                    'x-oriGin-host' => 'mytest.com',
                    'Example-Dict'  => 'a=1,    b=2;x=1;y=2,   c=(a   b   c), d ',
                ],
            ),
        );

        self::assertFalse($res->hasHeader('Example-Dictators'));
        self::assertTrue($res->hasHeader('x-oriGin-host'));
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     */
    public function testGetTrimmedHeaderValueThrows(): void
    {
        $this->expectExceptionObject(
            new InvalidHeaderKeyException(
                'The header "test-header-name" does not exist in the message. Note:headers can only be a part of the signing process if they are existing. $message->withHeader("test-header-name", \'your value\')',
            ),
        );
        $res = HeaderItemsDto::fromMessageInterface(
            new Request(
                'post',
                'localhost',
                [
                    'Host'          => 'localhost:8001',
                    'x-oriGin-host' => 'mytest.com',
                    'Example-Dict'  => 'a=1,    b=2;x=1;y=2,   c=(a   b   c), d ',
                ],
            ),
        );
        $res->getTrimmedHeaderValue('test-header-name');
    }
}

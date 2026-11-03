<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Support;

use Filefabrik\Rfc9421\Support\HeaderItems;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HeaderItems::class)]
final class HeaderItemsTest extends TestCase
{
    public function testFromInterface(): void
    {
        $res = HeaderItems::fromInterface(
            new Request(
                'post',
                'localhost',
                [
                    'Host'          => 'localhost:8001',
                    'x-oriGin-host' => 'mytest.com',
                    'Example-Dict'  => 'a=1,    b=2;x=1;y=2,   c=(a   b   c), d',
                ],
            ),
        );
        self::assertSame(
            [
                'host'          => 'localhost:8001',
                'x-origin-host' => 'mytest.com',
                'example-dict'  => 'a=1,    b=2;x=1;y=2,   c=(a   b   c), d',
            ],
            $res->toArray(),
        );
    }

    public function testToArray(): void
    {
        $res = HeaderItems::fromInterface(
            new Request(
                'post',
                'localhost',
                [
                    'Host'          => 'localhost:8001',
                    'x-oriGin-host' => 'mytest.com',
                    'Example-Dict'  => 'a=1,    b=2;x=1;y=2,   c=(a   b   c), d',
                ],
            ),
        );
        self::assertSame(
            [
                'host'          => 'localhost:8001',
                'x-origin-host' => 'mytest.com',
                'example-dict'  => 'a=1,    b=2;x=1;y=2,   c=(a   b   c), d',
            ],
            $res->toArray(),
        );
    }

    public function test__construct(): void
    {
        $instance = new HeaderItems([
            'Host'          => 'localhost:8001',
            'x-oriGin-host' => 'mytest.com',
            'Example-Dict'  => 'a=1,    b=2;x=1;y=2,   c=(a   b   c), d',
        ], );
        self::assertSame(
            [
                'host'          => 'localhost:8001',
                'x-origin-host' => 'mytest.com',
                'example-dict'  => 'a=1,    b=2;x=1;y=2,   c=(a   b   c), d',
            ],
            $instance->toArray(),
        );
    }

    public function testFromArray(): void
    {
        $instance = HeaderItems::fromArray([
            'Host'          => 'localhost:8001',
            'x-oriGin-host' => 'mytest.com',
            'Example-Dict'  => 'a=1,    b=2;x=1;y=2,   c=(a   b   c), d',
        ]);
        self::assertSame(
            [
                'host'          => 'localhost:8001',
                'x-origin-host' => 'mytest.com',
                'example-dict'  => 'a=1,    b=2;x=1;y=2,   c=(a   b   c), d',
            ],
            $instance->toArray(),
        );
    }
}

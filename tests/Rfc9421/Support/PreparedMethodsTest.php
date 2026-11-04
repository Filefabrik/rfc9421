<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Support;

use Closure;
use Filefabrik\Rfc9421\StructuredFields\Entry;
use Filefabrik\Rfc9421\Support\PreparedMethods;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use TypeError;
use UnexpectedValueException;

#[CoversClass(PreparedMethods::class)]
final class PreparedMethodsTest extends TestCase
{
    public function testGetMethod(): void
    {
        $i = new PreparedMethods();
        $i->adds(
            [
                'texst'  => fn (MessageInterface $message): string => $message->getMethod(),
                'poster' => fn (MessageInterface $message): string => $message->getMethod(),
            ],
        );
        self::assertInstanceOf(Closure::class, $i->getMethod('poster'));
    }

    public function testGetMethodThrow(): void
    {
        $this->expectExceptionObject(new UnexpectedValueException('Method "poster2" not registered.'));
        $i = new PreparedMethods();
        $i->adds(
            [
                'texst'  => fn (MessageInterface $message): string => $message->getMethod(),
                'poster' => fn (MessageInterface $message): string => $message->getMethod(),
            ],
        );

        self::assertFalse($i->getMethod('poster2'));
    }

    public function testAdds(): void
    {
        $arr = [
            'texst'  => fn (MessageInterface $message): string => $message->getMethod(),
            'poster' => fn (MessageInterface $message): string => $message->getMethod(),
        ];
        $i   = new PreparedMethods();
        $i->adds(
            $arr,
        );
        self::assertSame($arr, $i->methods);
    }

    public function testAddsThrow(): void
    {
        $this->expectException(TypeError::class);
        $arr = [
            'texst' => fn (MessageInterface $message): string => $message->getMethod(),
            'poster',
            'bullshit',
        ];

        new PreparedMethods()->adds(
            $arr,
        );
    }

    public function testHasMethod(): void
    {
        $arr = [
            'texst'  => fn (MessageInterface $message): string => $message->getMethod(),
            'poster' => fn (MessageInterface $message): string => $message->getMethod(),
        ];
        $i   = new PreparedMethods();
        $i->adds(
            $arr,
        );
        $i->add('poster2', fn (MessageInterface $message) => $message->getMethod());
        self::assertTrue($i->hasMethod('poster2'));
    }

    public function testAdd(): void
    {
        $arr = [
            'texst'  => fn (MessageInterface $message): string => $message->getMethod(),
            'poster' => fn (MessageInterface $message): string => $message->getMethod(),
        ];
        $i   = new PreparedMethods();
        $i->adds(
            $arr,
        );
        $i->add('poster2', fn (MessageInterface $message) => $message->getMethod());
        self::assertTrue($i->hasMethod('poster2'));
    }

    public function testCallWith(): void
    {
        $r = new Request(
            'post',
            'https://Test.com/var/pathy/?qer=var',
            body: '{"busy": true, "message": "Your call is very important to us"}',
        );

        $prepareMethods = new PreparedMethods();
        $prepareMethods->adds(
            [
                'content-length' => fn (MessageInterface $messageInterface): int => strlen(
                    (string)$messageInterface->getBody(),
                ),
            ],
        );
        $calledEntry = $prepareMethods->callWith('content-length', [$r]);
        self::assertSame(62, $calledEntry->value);
        self::assertSame('content-length', $calledEntry->key);
    }

    public function testCallWithReturnEntry(): void
    {
        $r = new Request(
            'post',
            'https://Test.com/var/pathy/?qer=var',
            body: '{"busy": true, "message": "Your call is very important to us"}',
        );

        $prepareMethods = new PreparedMethods();
        $prepareMethods->adds(
            [
                'content-length' => fn (MessageInterface $messageInterface): Entry => new Entry(
                    'content-length',
                    strlen((string)$messageInterface->getBody()),
                ),
            ],
        );
        $calledEntry = $prepareMethods->callWith('content-length', [$r]);
        self::assertSame(62, $calledEntry->value);
        self::assertSame('content-length', $calledEntry->key);
    }

}

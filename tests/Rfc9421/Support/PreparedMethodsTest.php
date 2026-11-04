<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Support;

use Closure;
use Filefabrik\Rfc9421\Exceptions\PrepareMethodsException;
use Filefabrik\Rfc9421\StructuredFields\Entry;
use Filefabrik\Rfc9421\Support\PreparedMethods;
use GuzzleHttp\Psr7\Request;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use TypeError;

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

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     */
    public function testGetMethodThrow(): void
    {
        $this->expectExceptionObject(new PrepareMethodsException('Method "poster2" not registered.'));
        $i = new PreparedMethods();
        $i->adds(
            [
                'texst'  => fn (MessageInterface $message): string => $message->getMethod(),
                'poster' => fn (MessageInterface $message): string => $message->getMethod(),
            ],
        );

        $i->getMethod('poster2');
    }
    public function testCallWithThrowMessag(): void
    {
        $this->expectExceptionObject(new PrepareMethodsException('Error during calling the method with key: "texst": something buggy inside'));
        $i = new PreparedMethods();
        $i->adds(
            [
                'texst'  => fn (): string => throw new InvalidArgumentException('something buggy inside'),
                'poster' => fn (MessageInterface $message): string => $message->getMethod(),
            ],
        );

        $i->callWith('texst',[]);
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

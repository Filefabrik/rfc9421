<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Support;

use Closure;
use Filefabrik\Rfc9421\Support\PreparedMethods;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use UnexpectedValueException;

#[CoversClass(PreparedMethods::class)]
class PreparedMethodsTest extends TestCase
{

    public function testGetMethod(): void
    {
        $i = new PreparedMethods();
        $i->adds(
            [
                'texst'  => fn(MessageInterface $message): string => $message->getMethod(),
                'poster' => fn(MessageInterface $message): string => $message->getMethod(),
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
                'texst'  => fn(MessageInterface $message): string => $message->getMethod(),
                'poster' => fn(MessageInterface $message): string => $message->getMethod(),
            ],
        );

        self::assertFalse($i->getMethod('poster2'));
    }

    public function testAdds(): void
    {
        $arr = [
            'texst'  => fn(MessageInterface $message): string => $message->getMethod(),
            'poster' => fn(MessageInterface $message): string => $message->getMethod(),
        ];
        $i   = new PreparedMethods();
        $i->adds(
            $arr,
        );
        self::assertSame($arr, $i->methods);
    }

    public function testAddsThrow(): void
    {
        $this->expectExceptionObject(new UnexpectedValueException('unknown array structure'));
        $arr = [
            'texst'  => fn(MessageInterface $message): string => $message->getMethod(),
            'poster' , fn(MessageInterface $message): string => $message->getMethod(),
        ];

        new PreparedMethods()->adds(
            $arr,
        );

    }

    public function testHasMethod(): void
    {
        $arr = [
            'texst'  => fn(MessageInterface $message): string => $message->getMethod(),
            'poster' => fn(MessageInterface $message): string => $message->getMethod(),
        ];
        $i   = new PreparedMethods();
        $i->adds(
            $arr,
        );
        $i->add('poster2', fn(MessageInterface $message) => $message->getMethod());
        self::assertTrue($i->hasMethod('poster2'));
    }

    public function testAdd(): void
    {
        $arr = [
            'texst'  => fn(MessageInterface $message): string => $message->getMethod(),
            'poster' => fn(MessageInterface $message): string => $message->getMethod(),
        ];
        $i   = new PreparedMethods();
        $i->adds(
            $arr,
        );
        $i->add('poster2', fn(MessageInterface $message) => $message->getMethod());
        self::assertTrue($i->hasMethod('poster2'));
    }

}

<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Signature;

use Filefabrik\Rfc9421\Signature\InitialContentsEnum;
use Filefabrik\Rfc9421\Signature\InitialContentsParameters;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

#[CoversClass(InitialContentsParameters::class)]
final class InitialContentsParametersTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function test__construct(): void
    {
        $t = new InitialContentsParameters(['sf' => null, 'req' => null]);
        self::assertSame(['sf' => null, 'req' => null], $t->parameters);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function testSetParameter(): void
    {
        $t = new InitialContentsParameters();
        $t->setParameter('name', '"baz"');
        self::assertSame(['name' => '"baz"'], $t->parameters);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function testGetValueOrThrow(): void
    {
        $t = new InitialContentsParameters();
        $t->setParameter('name', '"baz"');
        $t->setParameter('sf');
        self::assertSame('"baz"', $t->getValueOrThrow(InitialContentsEnum::name));
        self::assertNull($t->getValueOrThrow(InitialContentsEnum::sf));
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function testGetValueOrThrowTThrows(): void
    {
        $this->expectExceptionObject(new UnexpectedValueException('Initial contents enum: "key" was not set before.'));
        $t = new InitialContentsParameters();
        $t->setParameter('name', '"baz"')
          ->setParameter('sf')
        ;

        self::assertNull($t->getValueOrThrow(InitialContentsEnum::key));
    }

    public function testHas(): void
    {
        $t = new InitialContentsParameters();
        $t->setParameter('name', '"baz"');
        self::assertTrue($t->has('name'));
        self::assertFalse($t->has('sf'));
    }
}

<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Signature;

use Filefabrik\Rfc9421\Exceptions\EnumTypeException;
use Filefabrik\Rfc9421\Signature\InitialContentsEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InitialContentsEnum::class)]
final class InitialContentsEnumTest extends TestCase
{
    public function testIsOwnTypeOrNull(): void
    {
        self::assertNull(InitialContentsEnum::isOwnTypeOrNull('nothing'));
        self::assertEquals(InitialContentsEnum::tr, InitialContentsEnum::isOwnTypeOrNull('tr'));
        self::assertEquals(InitialContentsEnum::tr, InitialContentsEnum::isOwnTypeOrNull(InitialContentsEnum::tr));
    }

    public function testNames(): void
    {
        self::assertEquals(['sf', 'key', 'bs', 'tr', 'req', 'name'], InitialContentsEnum::names());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function testIsOwnTypeOrNullOrThrow(): void
    {
        self::assertEquals(InitialContentsEnum::tr, InitialContentsEnum::isOwnTypeOrNullOrThrow('tr'));
        self::assertEquals(
            InitialContentsEnum::tr,
            InitialContentsEnum::isOwnTypeOrNullOrThrow(InitialContentsEnum::tr),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function testIsOwnTypeOrNullOrThrowException(): void
    {
        $this->expectExceptionObject(
            new EnumTypeException('"nothing" is not a valid enumeration of "'.InitialContentsEnum::class.'" Enum.'),
        );
        InitialContentsEnum::isOwnTypeOrNullOrThrow('nothing');
    }
}

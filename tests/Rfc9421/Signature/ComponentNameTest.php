<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Signature;

use Filefabrik\Rfc9421\Exceptions\EnumTypeException;
use Filefabrik\Rfc9421\Signature\ComponentName;
use Filefabrik\Rfc9421\Signature\DerivedComponentEnum;
use Filefabrik\Rfc9421\Signature\InitialContentsEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ComponentName::class)]
final class ComponentNameTest extends TestCase
{
    public function test__construct(): void
    {
        // todo does not work with structured fields
        $i = ComponentName::fromComponentName('test');
        self::assertEquals('test', $i->getItem()->value());

        $i = ComponentName::fromComponentName('"test55"');
        self::assertEquals('test55', $i->getItem()->value());

        $i = ComponentName::fromComponentName('"test55"');
        self::assertEquals('test55', $i->getItem()->value());

        $i = ComponentName::fromNameAndInitialContentEnum('caramba', InitialContentsEnum::name);
        self::assertEquals('caramba', $i->getItem()->value());
        self::assertEquals('"caramba";name', $i->getItem()->toHttpValue());

        // by string with @
        $i = ComponentName::fromNameAndInitialContentEnum('@status', InitialContentsEnum::name);
        self::assertEquals('@status', $i->getItem()->value());
    }

    public function test__constructThrow(): void
    {
        $this->expectExceptionObject(
            new EnumTypeException(
                '"@test" is not a valid enumeration of "'.DerivedComponentEnum::class.'" Enum.',
            ),
        );
        $t = ComponentName::fromComponentName('@test');
    }

    public function test__constructThrowWithQuoted(): void
    {
        $this->expectExceptionObject(
            new EnumTypeException(
                '"@test" is not a valid enumeration of "'.DerivedComponentEnum::class.'" Enum.',
            ),
        );
        $t = ComponentName::fromComponentName('"@test"');
    }

}

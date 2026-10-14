<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Signature;

use Filefabrik\Rfc9421\Exceptions\EnumTypeException;
use Filefabrik\Rfc9421\Signature\Component;
use Filefabrik\Rfc9421\Signature\InitialContentsEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Component::class)]
final class ComponentTest extends TestCase
{
    public function test__construct(): void
    {
        $i = new Component('test');
        self::assertEquals('test', $i->name);
        self::assertNull($i->initialContentsEnum);
        $i = new Component('test55', 'sf');
        self::assertEquals('test55', $i->name);
        self::assertEquals(InitialContentsEnum::sf, $i->initialContentsEnum);

        $i = new Component('caramba', InitialContentsEnum::name);
        self::assertEquals('caramba', $i->name);
        self::assertEquals(InitialContentsEnum::name, $i->initialContentsEnum);
    }

    public function test__constructThrow(): void
    {
        $this->expectExceptionObject(
            new EnumTypeException(
                '"bullshit" is not a valid enumeration of "'.InitialContentsEnum::class.'" Enum.',
            ),
        );
        $t = new Component('test', 'bullshit');
    }
}

<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Enums;

use Filefabrik\Rfc9421\Enums\InitialContentsEnum;
use Filefabrik\Rfc9421\Exceptions\EnumTypeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InitialContentsEnum::class)]
final class InitialContentsEnumTest extends TestCase
{
    public function testIsOwnTypeOrNull(): void
    {
        self::assertNull(InitialContentsEnum::isOwnTypeOrNull('nothing'));
        self::assertEquals(InitialContentsEnum::bs, InitialContentsEnum::isOwnTypeOrNull('bs'));
        self::assertEquals(InitialContentsEnum::sf, InitialContentsEnum::isOwnTypeOrNull(InitialContentsEnum::sf));
    }

    public function testNames(): void
    { // 'tr'   not implemented yet
        self::assertEquals(['sf', 'key', 'bs', 'req', 'name'], InitialContentsEnum::names());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function testIsOwnTypeOrNullOrThrow(): void
    {
        self::assertEquals(InitialContentsEnum::sf, InitialContentsEnum::isOwnTypeOrNullOrThrow('sf'));
        self::assertEquals(
            InitialContentsEnum::sf,
            InitialContentsEnum::isOwnTypeOrNullOrThrow(InitialContentsEnum::sf),
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

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function testIsOwnTypeOrNullOrThrowNull(): void
    {
        self::assertNull(InitialContentsEnum::isOwnTypeOrNullOrThrow(null));
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function testIsOwnTypeOrThrow(): void
    {
        self::assertSame(InitialContentsEnum::key, InitialContentsEnum::isOwnTypeOrThrow('key'));
        self::assertSame(InitialContentsEnum::key, InitialContentsEnum::isOwnTypeOrThrow(InitialContentsEnum::key));
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function testIsOwnTypeOrThrowThrows(): void
    {
        $this->expectExceptionObject(
            new EnumTypeException('"nothing" is not a valid enumeration of "'.InitialContentsEnum::class.'" Enum.'),
        );
        InitialContentsEnum::isOwnTypeOrThrow('nothing');
    }
}

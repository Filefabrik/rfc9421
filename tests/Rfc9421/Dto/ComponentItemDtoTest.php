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

use Bakame\Http\StructuredFields\Item;
use Exception;
use Filefabrik\Rfc9421\Dto\ComponentItemDto;
use Filefabrik\Rfc9421\Exceptions\UnexpectedValue;
use Filefabrik\Rfc9421\Signature\ComponentName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ComponentItemDto::class)]
final class ComponentItemDtoTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testGetHttpValue(): void
    {
        $i = ComponentItemDto::fromFieldItem(Item::fromHttpValue('"test";sf'));

        self::assertSame(
            '"test";sf',
            $i->getHttpValue(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws Exception
     */
    public function testFromFieldItem(): void
    {
        $i = ComponentItemDto::fromFieldItem(Item::fromHttpValue('"test";sf'));

        self::assertEquals(
            ComponentName::fromString('"test";sf'),
            $i,
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testGetItem(): void
    {
        $i = ComponentName::fromString('"test";sf')
                          ->getItem()
        ;
        self::assertEquals(
            ComponentName::fromString('"test";sf')
                         ->getItem(),
            $i,
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @throws Exception
     */
    public function testWithoutInitialContentsReq(): void
    {
        $i = ComponentItemDto::fromFieldItem(Item::fromHttpValue('"test";req'));
        self::assertSame(
            '"test"',
            $i->withoutInitialContentsReq()
              ->getHttpValue(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @throws Exception
     */
    public function testWithoutInitialContentsReqThrows(): void
    {
        $this->expectExceptionObject(new UnexpectedValue('Can not find initial contents enum "req"'));
        $i = ComponentItemDto::fromFieldItem(Item::fromHttpValue('"test";sf'));
        $i->withoutInitialContentsReq();
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @throws Exception
     */
    public function testHasInitialContentsReq(): void
    {
        $i = ComponentItemDto::fromFieldItem(Item::fromHttpValue('"test";sf'));

        self::assertFalse($i->hasInitialContentsReq());
        $i = ComponentItemDto::fromFieldItem(Item::fromHttpValue('"test";req'));

        self::assertTrue($i->hasInitialContentsReq());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @throws Exception
     */
    public function testHttpValueWithoutReq(): void
    {
        $i = ComponentItemDto::fromFieldItem(Item::fromHttpValue('"test";req'));

        self::assertSame('"test"', $i->httpValueWithoutReq());
    }

}

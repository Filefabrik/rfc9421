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

}

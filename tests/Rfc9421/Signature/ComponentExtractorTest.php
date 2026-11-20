<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Signature;

use Filefabrik\Rfc9421\Dto\ComponentItemDto;
use Filefabrik\Rfc9421\Enums\DerivedComponentEnum;
use Filefabrik\Rfc9421\Exceptions\EnumTypeException;
use Filefabrik\Rfc9421\Exceptions\SignatureExtractionException;
use Filefabrik\Rfc9421\Signature\ComponentBuilder;
use Filefabrik\Rfc9421\Signature\ComponentExtractor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ComponentExtractor::class)]
final class ComponentExtractorTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function test__construct(): void
    {
        // todo does not work with structured fields
        $i = ComponentExtractor::extractToComponentItemDto('test');
        self::assertEquals(
            'test',
            $i->getItem()
              ->value(),
        );

        $i = ComponentExtractor::extractToComponentItemDto('"test55"');
        self::assertEquals(
            'test55',
            $i->getItem()
              ->value(),
        );
        $i = ComponentExtractor::extractToComponentItemDto(DerivedComponentEnum::method);
        self::assertEquals(
            '@method',
            $i->getItem()
              ->value(),
        );

        $i = ComponentExtractor::extractToComponentItemDto('"test55"');
        self::assertEquals(
            'test55',
            $i->getItem()
              ->value(),
        );

        $i = ComponentItemDto::fromComponentBuilder(new ComponentBuilder('caramba')->withName('dings'));
        self::assertEquals(
            'caramba',
            $i->getItem()
              ->value(),
        );
        self::assertEquals(
            '"caramba";name="dings"',
            $i->getItem()
              ->toHttpValue(),
        );

        // by string with @
        $i = ComponentItemDto::fromComponentBuilder(new ComponentBuilder('@status')->withReq());

        self::assertEquals(
            '@status',
            $i->getItem()
              ->value(),
        );

        $i = ComponentExtractor::extractToComponentItemDto('"test55";name="kevin"');
        self::assertEquals(
            '"test55";name="kevin"',
            $i->getItem()
              ->toHttpValue(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function test__constructThrow(): void
    {
        $this->expectExceptionObject(
            new EnumTypeException(
                '"@test" is not a valid enumeration of "'.DerivedComponentEnum::class.'" Enum.',
            ),
        );
        ComponentExtractor::extractToComponentItemDto('@test');
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function test__constructThrowWithQuoted(): void
    {
        $this->expectExceptionObject(
            new EnumTypeException(
                '"@test" is not a valid enumeration of "'.DerivedComponentEnum::class.'" Enum.',
            ),
        );
        ComponentExtractor::extractToComponentItemDto('"@test"');
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testGetItemThrow(): void
    {
        $this->expectExceptionObject(
            new SignatureExtractionException(
                'Invalid signature line:"test55',
            ),
        );
        ComponentExtractor::extractToComponentItemDto('"test55')
                     ->getHttpValue()
        ;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testFromRegexThrow(): void
    {
        $this->expectExceptionObject(new SignatureExtractionException('Invalid signature line:;'));
        ComponentExtractor::extractToComponentItemDto(';');
    }



    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testExtractor()
    {
        $res = ComponentExtractor::extractToBuilder('"caramba"');
        self::assertSame('"caramba"', (string)$res);

        $res = ComponentExtractor::extractToBuilder('"caramba";req');
        self::assertSame('"caramba";req', (string)$res);

        $res = ComponentExtractor::extractToBuilder('"caramba";req;name="player"');
        self::assertSame('"caramba";req;name="player"', (string)$res);

        $res = ComponentExtractor::extractToBuilder('"caramba";name="player";req');
        self::assertSame('"caramba";name="player";req', (string)$res);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testThrowMissingSemicolon(): void
    {
        $this->expectExceptionObject(new SignatureExtractionException('Invalid signature parameter:req'));
        ComponentExtractor::extractToBuilder('"caramba"req');
    }
}

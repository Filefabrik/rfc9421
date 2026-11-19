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
use Filefabrik\Rfc9421\Signature\ComponentEncoder;
use Filefabrik\Rfc9421\Signature\ComponentName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ComponentName::class)]
final class ComponentNameTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function test__construct(): void
    {
        // todo does not work with structured fields
        $i = ComponentName::fromString('test');
        self::assertEquals(
            'test',
            $i->getItem()
              ->value(),
        );

        $i = ComponentName::fromString('"test55"');
        self::assertEquals(
            'test55',
            $i->getItem()
              ->value(),
        );
        $i = ComponentName::fromString(DerivedComponentEnum::method);
        self::assertEquals(
            '@method',
            $i->getItem()
              ->value(),
        );

        $i = ComponentName::fromString('"test55"');
        self::assertEquals(
            'test55',
            $i->getItem()
              ->value(),
        );

        $i = ComponentItemDto::fromComponentEncoder(new ComponentEncoder('caramba')->withName('dings'));
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
        $i = ComponentItemDto::fromComponentEncoder(new ComponentEncoder('@status')->withReq());

        self::assertEquals(
            '@status',
            $i->getItem()
              ->value(),
        );

        $i = ComponentName::fromString('"test55";name="kevin"');
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
        ComponentName::fromString('@test');
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
        ComponentName::fromString('"@test"');
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
        ComponentName::fromString('"test55')->getHttpValue();
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testFromRegex4Segments(): void
    {
        $i = ComponentName::withName('caramba', 'myName')
                          ->getHttpValue()
        ;

        self::assertSame(
            '"caramba";name="myName"',
            ComponentName::fromString($i)
                         ->getHttpValue(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testFromRegexThrow(): void
    {
        $this->expectExceptionObject(new SignatureExtractionException('Invalid signature line:;'));
        ComponentName::fromString(';');
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testWithSf(): void
    {
        $i = ComponentName::withSf('caramba');
        self::assertEquals('"caramba";sf', $i->getHttpValue());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testWithSfReq(): void
    {
        $i = ComponentName::withSf('caramba', true);
        self::assertEquals('"caramba";sf;req', $i->getHttpValue());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testWithBs(): void
    {
        $i = ComponentName::withBs('caramba');
        self::assertEquals('"caramba";bs', $i->getHttpValue());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testWithBsReq(): void
    {
        $i = ComponentName::withBs('caramba', true);
        self::assertEquals('"caramba";bs;req', $i->getHttpValue());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testWithName(): void
    {
        $i = ComponentName::withName('caramba', 'a');
        self::assertEquals('"caramba";name="a"', $i->getHttpValue());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testWithNameReq(): void
    {
        $i = ComponentName::withName('caramba', 'a', true);
        self::assertEquals('"caramba";name="a";req', $i->getHttpValue());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testWithKey(): void
    {
        $i = ComponentName::withKey('caramba', 'a');
        self::assertEquals('"caramba";key="a"', $i->getHttpValue());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testWithKeyReq(): void
    {
        $i = ComponentName::withKey('caramba', 'a', true);
        self::assertEquals('"caramba";key="a";req', $i->getHttpValue());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testWithComponent(): void
    {
        $i = ComponentName::withComponent('caramba');
        self::assertEquals('"caramba"', $i->getHttpValue());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testWithComponentReq(): void
    {
        $i = ComponentName::withComponent('caramba', true);
        self::assertEquals('"caramba";req', $i->getHttpValue());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testExtractor()
    {
        $res = ComponentName::extractor('"caramba"');
        self::assertSame('"caramba"', (string)$res);

        $res = ComponentName::extractor('"caramba";req');
        self::assertSame('"caramba";req', (string)$res);

        $res = ComponentName::extractor('"caramba";req;name="player"');
        self::assertSame('"caramba";req;name="player"', (string)$res);

        $res = ComponentName::extractor('"caramba";name="player";req');
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
         ComponentName::extractor('"caramba"req');


    }
}

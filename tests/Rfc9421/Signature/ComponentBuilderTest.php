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

use Bakame\Http\StructuredFields\Item;
use Exception;
use Filefabrik\Rfc9421\Dto\ComponentItemDto;
use Filefabrik\Rfc9421\Enums\DerivedComponentEnum;
use Filefabrik\Rfc9421\Enums\InitialContentsEnum;
use Filefabrik\Rfc9421\Exceptions\UnexpectedValue;
use Filefabrik\Rfc9421\Signature\ComponentBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ComponentBuilder::class)]
final class ComponentBuilderTest extends TestCase
{
    /*    public function testErrors(): void
        {
        }*/
    public static function detects(): array
    {
        return [
            [InitialContentsEnum::key, 'testv', '"test-fieldname";key="testv"'],
            [InitialContentsEnum::name, 'testb', '"test-fieldname";name="testb"'],
            [InitialContentsEnum::bs, 'testb', '"test-fieldname";bs'],
            [InitialContentsEnum::req, 'testb', '"test-fieldname";req'],
            [InitialContentsEnum::sf, 'testb', '"test-fieldname";sf'],
        ];
    }

    public static function empties(): array
    {
        return [[null], ['']];
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testGetFieldNameString(): void
    {
        self::assertSame(
            'test-play',
            new ComponentBuilder('test-play')->setWithValue(InitialContentsEnum::key, 'key.va')
                                             ->getFieldNameString(),
        );
        self::assertSame(
            '@status',
            new ComponentBuilder(DerivedComponentEnum::status)->setWithValue(InitialContentsEnum::key, 'key.va')
                                                              ->getFieldNameString(),
        );
        self::assertSame(
            '@status',
            new ComponentBuilder(DerivedComponentEnum::status)->setWithValue(InitialContentsEnum::key, 'key.va')
                                                              ->getFieldNameString(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function testFrom()
    {
        self::assertSame(
            'test-play',
            ComponentBuilder::from('test-play')
                            ->getFieldNameString(),
        );
        self::assertSame(
            '@status',
            ComponentBuilder::from('@status')
                            ->getFieldNameString(),
        );
        self::assertSame(
            '@status',
            ComponentBuilder::from(DerivedComponentEnum::status)
                            ->getFieldNameString(),
        );
    }

    public function test__construct(): void
    {
        self::assertSame('"@authority"', (string)new ComponentBuilder(DerivedComponentEnum::authority));
        self::assertSame('"@authority";req', (string)new ComponentBuilder(DerivedComponentEnum::authority)->withReq());
        self::assertSame('"@authority"', (string)new ComponentBuilder('@authority'));
        self::assertSame('"@authority";req', (string)new ComponentBuilder('@authority')->withReq());

        self::assertSame('"@authority"', (string)new ComponentBuilder('"@authority"'));
        self::assertSame('"@authority";req', (string)new ComponentBuilder('"@authority"')->withReq());

        self::assertSame('"content-length"', (string)new ComponentBuilder('content-length'));
        self::assertSame('"content-length";req', (string)new ComponentBuilder('content-length')->withReq());
        self::assertSame('"content-length";bs', (string)new ComponentBuilder('content-length')->withBs());
        self::assertSame('"content-length";sf', (string)new ComponentBuilder('content-length')->withSf());
        self::assertSame(
            '"content-length";req;bs',
            (string)new ComponentBuilder('content-length')->withReq()
                                                          ->withBs(),
        );
        self::assertSame(
            '"content-length";sf;req',
            (string)new ComponentBuilder('content-length')->withSf()
                                                          ->withReq(),
        );

        self::assertSame(
            '"example-header";key="test-play"',
            (string)new ComponentBuilder('example-header')->withKey('test-play'),
        );
        self::assertSame(
            '"example-header";req;key="test-play"',
            (string)new ComponentBuilder('example-header')->withReq()
                                                          ->withKey('test-play'),
        );

        self::assertSame(
            '"example-header";name="test-play"',
            (string)new ComponentBuilder('example-header')->withName('test-play'),
        );
        self::assertSame(
            '"example-header";req;name="test-play"',
            (string)new ComponentBuilder('example-header')->withReq()
                                                          ->withName('test-play'),
        );
    }

    public function testSetReq(): void
    {
        self::assertSame('"test-play";req', (string)new ComponentBuilder('test-play')->setReq(true));
        self::assertSame('"test-play"', (string)new ComponentBuilder('test-play')->setReq(false));
        self::assertSame('"test-play"', (string)new ComponentBuilder('test-play')->setReq(null));
        self::assertSame('"test-play"', (string)new ComponentBuilder('test-play')->setReq());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testSetValueless(): void
    {
        self::assertSame(
            '"test-play";sf',
            (string)new ComponentBuilder('test-play')->setValueless(InitialContentsEnum::sf),
        );
        self::assertSame(
            '"test-play";req',
            (string)new ComponentBuilder('test-play')->setValueless(InitialContentsEnum::req),
        );
        self::assertSame(
            '"test-play";bs',
            (string)new ComponentBuilder('test-play')->setValueless(InitialContentsEnum::bs),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testSetWithValue(): void
    {
        self::assertSame(
            '"test-play";key="key.va"',
            (string)new ComponentBuilder('test-play')->setWithValue(InitialContentsEnum::key, 'key.va'),
        );
        self::assertSame(
            '"test-play";name="name.va"',
            (string)new ComponentBuilder('test-play')->setWithValue(InitialContentsEnum::name, 'name.va'),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testSetWithValueThrowsWrongEnum(): void
    {
        $this->expectExceptionObject(new UnexpectedValue('"bs" can not be set with an value'));
        (string)new ComponentBuilder('test-play')->setWithValue(InitialContentsEnum::bs, 'key.va');
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    #[DataProvider('empties')]
    public function testSetWithValueThrowsCanNotBeEmpty($v): void
    {
        $this->expectExceptionObject(new UnexpectedValue('Value can not be empty'));
        (string)new ComponentBuilder('test-play')->setWithValue(InitialContentsEnum::name, $v);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testSetValuelessThrows(): void
    {
        $this->expectExceptionObject(new UnexpectedValue('"key" is not part of valueless'));
        (string)new ComponentBuilder('test-play')->setValueless(InitialContentsEnum::key);
    }

    /**
     * @throws Exception
     */
    #[DataProvider('detects')]
    public function testSetDetect(InitialContentsEnum $contentsEnum, $value = null, $expected = null): void
    {
        self::assertSame($expected, (string)new ComponentBuilder('test-fieldname')->setDetect($contentsEnum, $value));
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testToComponentItemDto(): void
    {
        self::assertEquals(
            ComponentItemDto::fromFieldItem(Item::fromString('test-fieldname')),
            new ComponentBuilder('test-fieldname')->toComponentItemDto(),
        );
    }
}

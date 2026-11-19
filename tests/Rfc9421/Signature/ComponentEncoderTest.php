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
use Filefabrik\Rfc9421\Signature\ComponentEncoder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ComponentEncoder::class)]
final class ComponentEncoderTest extends TestCase
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

    public function test__construct(): void
    {
        self::assertSame('"@authority"', (string)new ComponentEncoder(DerivedComponentEnum::authority));
        self::assertSame('"@authority";req', (string)new ComponentEncoder(DerivedComponentEnum::authority)->withReq());
        self::assertSame('"@authority"', (string)new ComponentEncoder('@authority'));
        self::assertSame('"@authority";req', (string)new ComponentEncoder('@authority')->withReq());

        self::assertSame('"@authority"', (string)new ComponentEncoder('"@authority"'));
        self::assertSame('"@authority";req', (string)new ComponentEncoder('"@authority"')->withReq());

        self::assertSame('"content-length"', (string)new ComponentEncoder('content-length'));
        self::assertSame('"content-length";req', (string)new ComponentEncoder('content-length')->withReq());
        self::assertSame('"content-length";bs', (string)new ComponentEncoder('content-length')->withBs());
        self::assertSame('"content-length";sf', (string)new ComponentEncoder('content-length')->withSf());
        self::assertSame(
            '"content-length";req;bs',
            (string)new ComponentEncoder('content-length')->withReq()
                                                          ->withBs(),
        );
        self::assertSame(
            '"content-length";sf;req',
            (string)new ComponentEncoder('content-length')->withSf()
                                                          ->withReq(),
        );

        self::assertSame(
            '"example-header";key="test-play"',
            (string)new ComponentEncoder('example-header')->withKey('test-play'),
        );
        self::assertSame(
            '"example-header";req;key="test-play"',
            (string)new ComponentEncoder('example-header')->withReq()
                                                          ->withKey('test-play'),
        );

        self::assertSame(
            '"example-header";name="test-play"',
            (string)new ComponentEncoder('example-header')->withName('test-play'),
        );
        self::assertSame(
            '"example-header";req;name="test-play"',
            (string)new ComponentEncoder('example-header')->withReq()
                                                          ->withName('test-play'),
        );
    }

    public function testSetReq(): void
    {
        self::assertSame('"test-play";req', (string)new ComponentEncoder('test-play')->setReq(true));
        self::assertSame('"test-play"', (string)new ComponentEncoder('test-play')->setReq(false));
        self::assertSame('"test-play"', (string)new ComponentEncoder('test-play')->setReq(null));
        self::assertSame('"test-play"', (string)new ComponentEncoder('test-play')->setReq());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testSetValueless(): void
    {
        self::assertSame(
            '"test-play";sf',
            (string)new ComponentEncoder('test-play')->setValueless(InitialContentsEnum::sf),
        );
        self::assertSame(
            '"test-play";req',
            (string)new ComponentEncoder('test-play')->setValueless(InitialContentsEnum::req),
        );
        self::assertSame(
            '"test-play";bs',
            (string)new ComponentEncoder('test-play')->setValueless(InitialContentsEnum::bs),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testSetWithValue(): void
    {
        self::assertSame(
            '"test-play";key="key.va"',
            (string)new ComponentEncoder('test-play')->setWithValue(InitialContentsEnum::key, 'key.va'),
        );
        self::assertSame(
            '"test-play";name="name.va"',
            (string)new ComponentEncoder('test-play')->setWithValue(InitialContentsEnum::name, 'name.va'),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testSetWithValueThrowsWrongEnum(): void
    {
        $this->expectExceptionObject(new UnexpectedValue('"bs" can not be set with an value'));
        (string)new ComponentEncoder('test-play')->setWithValue(InitialContentsEnum::bs, 'key.va');
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    #[DataProvider('empties')]
    public function testSetWithValueThrowsCanNotBeEmpty($v): void
    {
        $this->expectExceptionObject(new UnexpectedValue('Value can not be empty'));
        (string)new ComponentEncoder('test-play')->setWithValue(InitialContentsEnum::name, $v);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testSetValuelessThrows(): void
    {
        $this->expectExceptionObject(new UnexpectedValue('"key" is not part of valueless'));
        (string)new ComponentEncoder('test-play')->setValueless(InitialContentsEnum::key);
    }

    /**
     * @throws Exception
     */
    #[DataProvider('detects')]
    public function testSetDetect(InitialContentsEnum $contentsEnum, $value = null, $expected = null): void
    {
        self::assertSame($expected, (string)new ComponentEncoder('test-fieldname')->setDetect($contentsEnum, $value));
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testToComponentItemDto(): void
    {
        self::assertEquals(
            ComponentItemDto::fromFieldItem(Item::fromString('test-fieldname')),
            new ComponentEncoder('test-fieldname')->toComponentItemDto(),
        );
    }
}

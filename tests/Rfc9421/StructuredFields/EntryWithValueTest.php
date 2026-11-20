<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\StructuredFields;

use Filefabrik\Rfc9421\Signature\ComponentBuilder;
use Filefabrik\Rfc9421\StructuredFields\EntryWithValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EntryWithValue::class)]
final class EntryWithValueTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function testGetFieldName(): void
    {
        $entry = EntryWithValue::fromBuilder(
            builder: ComponentBuilder::from('testkey')
                                     ->withReq(),
            value  : 'myValue',
        );
        self::assertSame('testkey', $entry->getFieldName());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function testGetComponent(): void
    {
        $entry = EntryWithValue::fromBuilder(
            builder: ComponentBuilder::from('testkey')
                                     ->withName('testName')
                                     ->withReq(),
            value  : 'myValue',
        );
        self::assertSame('"testkey";name="testName";req', $entry->getComponent());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function testGetBuilder(): void
    {
        $builder = ComponentBuilder::from('testkey')
                                   ->withName('testName')
                                   ->withReq()
        ;
        $entry   = EntryWithValue::fromBuilder(
            builder: $builder,
            value  : 'myValue',
        );
        self::assertSame($builder, $entry->getBuilder());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testWithREQEnum(): void
    {
        $entry = EntryWithValue::fromBuilder(
            builder: ComponentBuilder::from('testkey')
                                     ->withReq(),
            value  : 'myValue',
        );
        self::assertSame('"testkey";req', $entry->getComponent());
        self::assertSame('myValue', $entry->value);
        self::assertSame('"testkey";req: myValue', $entry->parseComponentNameAndValue());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testSetValue(): void
    {
        $entry = EntryWithValue::fromBuilder(ComponentBuilder::from('testkey'));

        self::assertSame('"testkey"', $entry->getComponent());
        self::assertNull($entry->value);

        self::assertSame('"testkey": ', $entry->parseComponentNameAndValue());

        $newEntry = $entry->setValue('myValue');
        self::assertSame('myValue', $newEntry->value);
        self::assertSame('"testkey": myValue', $newEntry->parseComponentNameAndValue());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testSetValueWithReq(): void
    {
        $entry = EntryWithValue::fromBuilder(
            builder: ComponentBuilder::from('testkey')
                                     ->withReq(),
            value  : 'myValue',
        );

        $newEntry = $entry->setValue('myValue');

        // todo this is wrong because of response handles other
        self::assertSame('"testkey";req: myValue', $newEntry->parseComponentNameAndValue());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testParseComponentNameAndValue(): void
    {
        $entry = EntryWithValue::fromBuilder(
            ComponentBuilder::from('testkey')
                            ->withSf(),
        );
        self::assertSame('"testkey";sf: ', $entry->parseComponentNameAndValue());

        //todo with 'fieldName' parameter, that is wrong
        //  $entry = new Entry('cramaba', '"myName"', InitialContentsEnum::fieldName);
        //self::assertSame('"cramaba";fieldName="myName" ', $entry->parse());
        $targetEntry = EntryWithValue::fromBuilder(
            ComponentBuilder::from('testkey')
                            ->withKey('a'),
            '1',
        );

        self::assertSame('"testkey";key="a": 1', $targetEntry->parseComponentNameAndValue());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function test__construct(): void
    {
        $entry = EntryWithValue::fromBuilder(ComponentBuilder::from('testkey'));
        self::assertSame('"testkey": ', $entry->parseComponentNameAndValue());
    }
}

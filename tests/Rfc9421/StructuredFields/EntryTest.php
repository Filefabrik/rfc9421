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

use Filefabrik\Rfc9421\Enums\InitialContentsEnum;
use Filefabrik\Rfc9421\Exceptions\StructuredFieldException;
use Filefabrik\Rfc9421\StructuredFields\Entry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertSame;

#[CoversClass(Entry::class)]
final class EntryTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function testWithREQEnum(): void
    {
        $entry = new Entry('testkey', 'myValue')->withREQEnum();
        self::assertSame('"testkey": myValue', $entry->parse());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function testSetValue(): void
    {
        $entry    = new Entry('testkey');
        $newEntry = $entry->setValue('myValue');
        self::assertSame('"testkey": ', $entry->parse());
        self::assertSame('"testkey": myValue', $newEntry->parse());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     */
    public function testSetValueWithReq(): void
    {
        $entry    = new Entry('testkey')->withREQEnum();
        $newEntry = $entry->setValue('myValue');

        // todo this is wrong because of response handles other
        self::assertSame('"testkey": myValue', $newEntry->parse());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function testParse(): void
    {
        $entry = new Entry('testkey', null, InitialContentsEnum::sf);
        self::assertSame('"testkey";sf: ', $entry->parse());

        //todo with 'name' parameter, that is wrong
        //  $entry = new Entry('cramaba', '"myName"', InitialContentsEnum::name);
        //self::assertSame('"cramaba";name="myName" ', $entry->parse());

        $targetEntry = new Entry(
            key            : 'testkey',
            value          : '1',
            initialContents: InitialContentsEnum::key,
            nameOrKeyValue : 'a',
        );
        self::assertSame('"testkey";key="a": 1', $targetEntry->parse());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function test__construct(): void
    {
        $entry = new Entry('testkey');
        self::assertSame('"testkey": ', $entry->parse());
    }

    public function testSetInitialContents(): void
    {
        $entry = new Entry('testkey');
        self::assertNull($entry->initialContents);
        $entry->setInitialContents(InitialContentsEnum::sf);
        assertSame(InitialContentsEnum::sf, $entry->initialContents);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function testKeyParseThrows(): void
    {
        $this->expectExceptionObject(
            new StructuredFieldException('For the Entry-Field "testkey" there was no ;key="#missing#" key given.'),
        );
        new Entry(
            key            : 'testkey',
            value          : '1',
            initialContents: InitialContentsEnum::key,
        )->parse();
    }
}

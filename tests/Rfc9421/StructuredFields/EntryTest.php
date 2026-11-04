<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\StructuredFields;

use Filefabrik\Rfc9421\Enums\InitialContentsEnum;
use Filefabrik\Rfc9421\StructuredFields\Entry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertSame;

#[CoversClass(Entry::class)]
final class EntryTest extends TestCase
{
    public function testWithREQEnum(): void
    {
        $entry = new Entry('testkey', 'myValue')->withREQEnum();
        self::assertSame('"testkey": myValue', $entry->parse());
    }

    public function testSetValue(): void
    {
        $entry    = new Entry('testkey');
        $newEntry = $entry->setValue('myValue');
        self::assertSame('"testkey": ', $entry->parse());
        self::assertSame('"testkey": myValue', $newEntry->parse());
    }

    public function testSetValueWithReq(): void
    {
        $entry    = new Entry('testkey')->withREQEnum();
        $newEntry = $entry->setValue('myValue');

        // todo this is wrong because of response handles other
        self::assertSame('"testkey": myValue', $newEntry->parse());
    }

    public function testWithKeyMethod(): void
    {
        $entry = new Entry('testkey')->withKeyMethod(fn ($string): string => $string.'#');
        self::assertSame('testkey#: ', $entry->parse());
    }

    public function testParse(): void
    {
        $entry = new Entry('testkey', null, InitialContentsEnum::sf);
        self::assertSame('"testkey";sf: ', $entry->parse());

        //todo with 'name' parameter, that is wrong
        //  $entry = new Entry('cramaba', '"myName"', InitialContentsEnum::name);
        //self::assertSame('"cramaba";name="myName" ', $entry->parse());

        $targetEntry = new Entry('testkey', '"a": 1', InitialContentsEnum::key);
        self::assertSame('"testkey";key="a": 1', $targetEntry->parse());
    }

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
}

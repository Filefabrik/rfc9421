<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\StructuredFields\Processing;

use Filefabrik\Rfc9421\StructuredFields\Entry;
use Filefabrik\Rfc9421\StructuredFields\Processing\Key;
use Filefabrik\Rfc9421\StructuredFields\StructuredFields;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Key::class)]
final class KeyTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     */
    public function testHandleWithEmptyValue(): void
    {
        $structuredFields = new StructuredFields();
        $keyInstance      = new Key(structuredFieldTypes: $structuredFields);

        $entry = new Entry('example-dict');
        //$structuredFields
        $returnEntry = $keyInstance->handle(entry: $entry, keyName: 'b');
        self::assertSame('"example-dict";key="b": ', $returnEntry->parse());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     */
    public function testHandleWithValue(): void
    {
        $structuredFields = new StructuredFields();
        $structuredFields->add('example-dict', 'list');

        $keyInstance = new Key(structuredFieldTypes: $structuredFields);

        $entry = new Entry('example-dict', 'a=1,    b=2;x=1;y=2,   d ');
        //$structuredFields
        $returnEntry = $keyInstance->handle(entry: $entry, keyName: 'a');
        // starting from a
        self::assertSame('"example-dict";key="a": 1', $returnEntry->parse());

        $entry = new Entry('example-dict', 'a=1,    b=2;x=1;y=2,   d ');
        //$structuredFields
        $returnEntry = $keyInstance->handle(entry: $entry, keyName: 'b');
        // starting from b
        self::assertSame('"example-dict";key="b": 2;x=1;y=2', $returnEntry->parse());
    }

}

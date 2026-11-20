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

use Filefabrik\Rfc9421\Signature\ComponentBuilder;
use Filefabrik\Rfc9421\StructuredFields\EntryWithValue;
use Filefabrik\Rfc9421\StructuredFields\Processing\Key;
use Filefabrik\Rfc9421\StructuredFields\StructuredFields;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Key::class)]
final class KeyTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testHandleWithEmptyValue(): void
    {
        $structuredFields = new StructuredFields();
        $keyInstance      = new Key(structuredFieldTypes: $structuredFields);

        $entry = EntryWithValue::fromBuilder(ComponentBuilder::from('example-dict'));
        //$structuredFields
        $returnEntry = $keyInstance->handle(entry: $entry, keyName: 'b');
        self::assertSame('"example-dict";key="b"', $returnEntry->getComponent());
        self::assertSame('"example-dict";key="b": ', $returnEntry->parseComponentNameAndValue());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testHandleWithValue(): void
    {
        $structuredFields = new StructuredFields();
        $structuredFields->add('example-dict', 'list');

        $keyInstance = new Key(structuredFieldTypes: $structuredFields);
        $entry       = EntryWithValue::fromBuilder(ComponentBuilder::from('example-dict'), 'a=1,    b=2;x=1;y=2,   d ');

        //$structuredFields
        $returnEntry = $keyInstance->handle(entry: $entry, keyName: 'a');
        // starting from a
        self::assertSame('"example-dict";key="a": 1', $returnEntry->parseComponentNameAndValue());
        $entry = EntryWithValue::fromBuilder(ComponentBuilder::from('example-dict'), 'a=1,    b=2;x=1;y=2,   d ');

        //$structuredFields
        $returnEntry = $keyInstance->handle(entry: $entry, keyName: 'b');
        // starting from b
        self::assertSame('"example-dict";key="b": 2;x=1;y=2', $returnEntry->parseComponentNameAndValue());
    }

}

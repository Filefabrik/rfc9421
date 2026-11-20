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
use Filefabrik\Rfc9421\StructuredFields\Processing\Name;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Name::class)]
final class NameTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testHandle(): void
    {
        $firstEntry = EntryWithValue::fromBuilder(ComponentBuilder::from('query-param'), 'no-value');
        // stupid never existing example
        self::assertSame('"query-param": no-value', $firstEntry->parseComponentNameAndValue());
        self::assertSame('"query-param"', $firstEntry->getComponent());
        $handledEntry = new Name()->handle($firstEntry, 'thats-the-key');

        self::assertEquals(
            expected: EntryWithValue::fromBuilder(
                builder: ComponentBuilder::from('query-param')
                                         ->withName('thats-the-key'),
                value  : 'no-value',
            ),
            actual  : $handledEntry,
        );
        self::assertSame('"query-param";name="thats-the-key"', $handledEntry->getComponent());
        self::assertSame('no-value', $handledEntry->value);
        self::assertSame('"query-param";name="thats-the-key": no-value', $handledEntry->parseComponentNameAndValue());
    }
}

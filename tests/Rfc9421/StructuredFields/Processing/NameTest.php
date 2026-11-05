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

use Filefabrik\Rfc9421\Enums\InitialContentsEnum;
use Filefabrik\Rfc9421\StructuredFields\Entry;
use Filefabrik\Rfc9421\StructuredFields\Processing\Name;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Name::class)]
final class NameTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function testHandle(): void
    {
        $firstEntry = new Entry('query-param', 'no-value');
        // stupid never existing example
        self::assertSame('"query-param": no-value', $firstEntry->parse());
        $handledEntry = new Name()->handle($firstEntry, 'thats-the-key');
        self::assertEquals(
            new Entry(
                key           : 'query-param',
                value         : 'no-value',
                initialContents: InitialContentsEnum::name,
                nameOrKeyValue: 'thats-the-key',
            ),
            $handledEntry,
        );
        self::assertSame('"query-param";name="thats-the-key": no-value', $handledEntry->parse());
    }
}

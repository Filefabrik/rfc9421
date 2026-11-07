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
use Filefabrik\Rfc9421\StructuredFields\Processing\Bs;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Bs::class)]
final class BsTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     */
    public function testHandleEmpty(): void
    {
        $request = new Request('POST', '');
        // ['Example-Header']
        $entry       = new Entry('example-header');
        $bs          = new Bs();
        $returnEntry = $bs->handle($entry, $request);
        self::assertNull($returnEntry->value);
        self::assertSame('"example-header";bs: ', $returnEntry->parse());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     */
    public function testHandleWithOne(): void
    {
        $request = new Request('POST', '');
        $request = $request->withAddedHeader('example-header', 'my test value first');

        $entry       = new Entry('example-header');
        $bs          = new Bs();
        $returnEntry = $bs->handle($entry, $request);
        self::assertSame(':bXkgdGVzdCB2YWx1ZSBmaXJzdA==:', $returnEntry->value);
        self::assertSame('"example-header";bs: :bXkgdGVzdCB2YWx1ZSBmaXJzdA==:', $returnEntry->parse());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     */
    public function testHandleWithTwo(): void
    {
        $request = new Request('POST', '');
        $request = $request->withAddedHeader('example-header', 'my test value first');
        $request = $request->withAddedHeader('example-header', 'the second, test value first');
        // ['Example-Header']
        $entry       = new Entry('example-header');
        $bs          = new Bs();
        $returnEntry = $bs->handle($entry, $request);
        self::assertSame(
            ':bXkgdGVzdCB2YWx1ZSBmaXJzdA==:, :dGhlIHNlY29uZCwgdGVzdCB2YWx1ZSBmaXJzdA==:',
            $returnEntry->value,
        );
        self::assertSame(
            '"example-header";bs: :bXkgdGVzdCB2YWx1ZSBmaXJzdA==:, :dGhlIHNlY29uZCwgdGVzdCB2YWx1ZSBmaXJzdA==:',
            $returnEntry->parse(),
        );
    }
}

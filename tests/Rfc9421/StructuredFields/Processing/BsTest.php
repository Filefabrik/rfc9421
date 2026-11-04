<?php
/*
 * Copyright (c) 2026.  rt@filefabrik.com
 * proprietary
 * todo insert correct copyright
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

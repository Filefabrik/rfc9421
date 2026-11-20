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
use Filefabrik\Rfc9421\StructuredFields\Processing\Bs;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Bs::class)]
final class BsTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testHandleEmpty(): void
    {
        $request = new Request('POST', '');
        // ['Example-Header']
        $entry       = EntryWithValue::fromBuilder(ComponentBuilder::from('example-header'));
        $bs          = new Bs();
        $returnEntry = $bs->handle($entry, $request);
        self::assertNull($returnEntry->getValue());
        self::assertSame('"example-header";bs', $returnEntry->getComponent());
        self::assertSame('"example-header";bs: ', $returnEntry->parseComponentNameAndValue());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testHandleWithOne(): void
    {
        $request = new Request('POST', '');
        $request = $request->withAddedHeader('example-header', 'my test value first');

        $entry       = EntryWithValue::fromBuilder(ComponentBuilder::from('example-header'));
        $bs          = new Bs();
        $returnEntry = $bs->handle($entry, $request);
        self::assertSame(':bXkgdGVzdCB2YWx1ZSBmaXJzdA==:', $returnEntry->getValue());
        self::assertSame('"example-header";bs', $returnEntry->getComponent());
        self::assertSame(
            '"example-header";bs: :bXkgdGVzdCB2YWx1ZSBmaXJzdA==:',
            $returnEntry->parseComponentNameAndValue(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testHandleWithTwo(): void
    {
        $request = new Request('POST', '');
        $request = $request->withAddedHeader('example-header', 'my test value first');
        $request = $request->withAddedHeader('example-header', 'the second, test value first');
        // ['Example-Header']
        $entry       = EntryWithValue::fromBuilder(ComponentBuilder::from('example-header'));
        $bs          = new Bs();
        $returnEntry = $bs->handle($entry, $request);
        self::assertSame(
            ':bXkgdGVzdCB2YWx1ZSBmaXJzdA==:, :dGhlIHNlY29uZCwgdGVzdCB2YWx1ZSBmaXJzdA==:',
            $returnEntry->getValue(),
        );
        self::assertSame(
            '"example-header";bs',
            $returnEntry->getComponent(),
        );
        self::assertSame(
            '"example-header";bs: :bXkgdGVzdCB2YWx1ZSBmaXJzdA==:, :dGhlIHNlY29uZCwgdGVzdCB2YWx1ZSBmaXJzdA==:',
            $returnEntry->parseComponentNameAndValue(),
        );
    }
}

<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Support;

use Filefabrik\Rfc9421\Support\UnquoteHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

#[CoversClass(UnquoteHelper::class)]
final class UnquoteHelperTest extends TestCase
{
    public function testUnquote(): void
    {
        $input = '"content-length"';
        self::assertSame('content-length', UnquoteHelper::unquote($input));
    }

    public function testUnquoteMissingThrow(): void
    {
        $this->expectExceptionObject(
            new UnexpectedValueException(
                'The expected value has to be wrapped by quotes "value" but is content-length.',
            ),
        );
        $input = 'content-length';
        UnquoteHelper::unquote($input);
    }

    public function testUnquoteMissingStartThrow(): void
    {
        $this->expectExceptionObject(
            new UnexpectedValueException(
                'The expected value has to be wrapped by quotes "value" but is "content-length.',
            ),
        );
        $input = '"content-length';
        UnquoteHelper::unquote($input);
    }

    public function testUnquoteMissingEndThrow(): void
    {
        $this->expectExceptionObject(
            new UnexpectedValueException(
                'The expected value has to be wrapped by quotes "value" but is content-length".',
            ),
        );
        $input = 'content-length"';
        UnquoteHelper::unquote($input);
    }

    public function testQuotedUnderscore(): void
    {
        self::assertSame('"_play_"', UnquoteHelper::quotedUnderscore('play'));
    }

    public function testStartsWithBrace(): void
    {
        self::assertTrue(UnquoteHelper::startsWithBrace('(foo'));
        self::assertFalse(UnquoteHelper::startsWithBrace('foo'));
    }

    public function testUnderscore(): void
    {
        self::assertSame('_foo_', UnquoteHelper::underscore('foo'));
    }

    public function testHasSurroundBraces(): void
    {
        self::assertFalse(UnquoteHelper::hasSurroundBraces('foo'));
        self::assertFalse(UnquoteHelper::hasSurroundBraces('(foo'));
        self::assertFalse(UnquoteHelper::hasSurroundBraces('foo)'));
        self::assertTrue(UnquoteHelper::hasSurroundBraces('(foo)'));
    }

    public function testRemoveSurroundingBraces(): void
    {
        self::assertSame('foo', UnquoteHelper::removeSurroundingBraces('(foo)'));
    }

    public function testRemoveSurroundingBracesThrowEnd(): void
    {
        $this->expectExceptionObject(new UnexpectedValueException('There was no brace ")" at the end value:"foo".'));
        self::assertSame('foo', UnquoteHelper::removeSurroundingBraces('(foo'));
    }

    public function testRemoveSurroundingBracesThrowStart(): void
    {
        $this->expectExceptionObject(
            new UnexpectedValueException('There was no brace "(" at the beginning value:"foo)".'),
        );
        self::assertSame('foo', UnquoteHelper::removeSurroundingBraces('foo)'));
    }

    public function testRemoveEndBrace(): void
    {
        self::assertSame('(foo', UnquoteHelper::removeEndBrace('(foo)'));
        self::assertSame('foo', UnquoteHelper::removeEndBrace('foo)'));
    }

    public function testRemoveStartBrace(): void
    {
        self::assertSame('foo)', UnquoteHelper::removeStartBrace('(foo)'));
        self::assertSame('foo', UnquoteHelper::removeStartBrace('(foo'));
    }

    public function testEndsWithBrace(): void
    {
        self::assertTrue(UnquoteHelper::endsWithBrace('(foo)'));
        self::assertTrue(UnquoteHelper::endsWithBrace('foo)'));
        self::assertFalse(UnquoteHelper::endsWithBrace('(foo'));
    }

    public function testQuote(): void
    {
        self::assertSame('"play"', UnquoteHelper::quote('play'));
    }

    public function testIsQuoted(): void
    {
        self::assertTrue(UnquoteHelper::isQuoted('"foo"'));
        self::assertFalse(UnquoteHelper::isQuoted('"foo'));
        self::assertFalse(UnquoteHelper::isQuoted('foo"'));
        self::assertFalse(UnquoteHelper::isQuoted('foo'));
    }

    public function testQuoteKEYValue(): void
    {
        self::assertSame('"play": name', UnquoteHelper::quoteKEYValue('play', 'name'));
    }

    public function testStartsWithQuote(): void
    {
        self::assertTrue(UnquoteHelper::startsWithQuote('"foo'));
        self::assertTrue(UnquoteHelper::startsWithQuote('"foo"'));
        self::assertFalse(UnquoteHelper::startsWithQuote('foo"'));
        self::assertFalse(UnquoteHelper::startsWithQuote('foo'));
    }
}

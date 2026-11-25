<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Signer\Signatures;

use Filefabrik\Rfc9421\Exceptions\UnexpectedValue;
use Filefabrik\Rfc9421\Signer\Signatures\Line;
use Filefabrik\Rfc9421\Signer\Signatures\ReadLines;
use Filefabrik\Rfc9421\Signer\Signatures\WriteLines;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ReadLines::class)]
final class ReadLinesTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testSetupArray(): void
    {
        $rl = new ReadLines(['foo' => 'bar', 'play' => 'book']);

        self::assertEquals(['foo' => 'bar', 'play' => 'book'], $rl->toKeyValueArray());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testReadWriteLines(): void
    {
        $wl = new WriteLines()->addEntry('foo', 'bar')
                              ->addEntry('play', 'book')
        ;

        $rl = new ReadLines($wl);

        self::assertEquals(['foo' => 'bar', 'play' => 'book'], $rl->toKeyValueArray());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testSetup(): void
    {
        $rl = new ReadLines([new Line('foo', 'bar'), new Line('play', 'book')]);

        self::assertEquals(['foo' => 'bar', 'play' => 'book'], $rl->toKeyValueArray());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testToLineString(): void
    {
        $rl = new ReadLines([new Line('"foo"', 'bar'), new Line('"play"', 'book')]);

        self::assertEquals(['"foo"' => '"foo": bar', '"play"' => '"play": book'], $rl->toLineString());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testWrongFormat(): void
    {
        $this->expectExceptionObject(new UnexpectedValue('Unexpected value of type string or key of type integer'));
        new ReadLines(['bar', 'book']);
    }
}

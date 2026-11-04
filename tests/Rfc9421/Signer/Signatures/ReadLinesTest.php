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

use Filefabrik\Rfc9421\Signer\Signatures\ReadLines;
use Filefabrik\Rfc9421\Signer\Signatures\WriteLines;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ReadLines::class)]
final class ReadLinesTest extends TestCase
{
    public function testSetupArray(): void
    {
        $rl = new ReadLines(['foo' => 'bar', 'play' => 'book']);

        self::assertEquals(['foo' => 'bar', 'play' => 'book'], $rl->toKeyValueArray());
    }

    public function testReadWriteLines(): void
    {
        $wl = new WriteLines()->addEntry('foo', 'bar')
                              ->addEntry('play', 'book')
        ;

        $rl = new ReadLines($wl);

        self::assertEquals(['foo' => 'bar', 'play' => 'book'], $rl->toKeyValueArray());
    }
}

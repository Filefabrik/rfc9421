<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Signer\Signatures;

use Filefabrik\Rfc9421\Signer\Signatures\Line;
use Filefabrik\Rfc9421\Signer\Signatures\WriteLines;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WriteLines::class)]
final class WriteLinesTest extends TestCase
{
    public function testToArray(): void
    {
        $sl = new WriteLines();
        $sl->add(new Line('foo', 'bar'));
        self::assertEquals([['foo', 'bar']], $sl->toArray());
    }

    public function testToKeyValueArray(): void
    {
        $sl = new WriteLines();
        $sl->add(new Line('foo', 'bar'));
        self::assertEquals(['foo' => 'bar'], $sl->toKeyValueArray());
    }

    public function testGetLineNumber(): void
    {
        $sl = new WriteLines();
        $sl->add(new Line('foo', 'bar'));
        self::assertEquals(new Line('foo', 'bar'), $sl->getLineNumber(0));
    }

    public function testAdd(): void
    {
        $sl = new WriteLines();
        $sl->add(new Line('foo', 'bar'));
        self::assertEquals(new Line('foo', 'bar'), $sl->getLineNumber(0));
    }

    public function testAddEntry(): void
    {
        $sl = new WriteLines();
        $sl->addEntry('foo', 'bar');
        self::assertEquals(new Line('foo', 'bar'), $sl->getLineNumber(0));
    }
}

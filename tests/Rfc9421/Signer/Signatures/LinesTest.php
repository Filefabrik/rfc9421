<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Signer\Signatures;

use Filefabrik\Rfc9421\Signer\Signatures\Line;
use Filefabrik\Rfc9421\Signer\Signatures\Lines;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Filefabrik\Rfc9421\Signer\Signatures\Lines::class)]
class LinesTest extends TestCase
{

    public function testToArray(): void
    {
        $sl = new Lines();
        $sl->add(new Line('foo', 'bar'));
        self::assertEquals([['foo', 'bar']], $sl->toArray());
    }
    public function testToKeyValueArray(): void
    {
        $sl = new Lines();
        $sl->add(new Line('foo', 'bar'));
        self::assertEquals(['foo'=> 'bar'], $sl->toKeyValueArray());
    }

    public function testGetLineNumber(): void
    {
        $sl = new Lines();
        $sl->add(new Line('foo', 'bar'));
        self::assertEquals(new Line('foo', 'bar'), $sl->getLineNumber(0));
    }

    public function testAdd(): void
    {
        $sl = new Lines();
        $sl->add(new Line('foo', 'bar'));
        self::assertEquals(new Line('foo', 'bar'), $sl->getLineNumber(0));
    }

    public function testAddEntry(): void
    {
        $sl = new Lines();
        $sl->addEntry('foo', 'bar');
        self::assertEquals(new Line('foo', 'bar'), $sl->getLineNumber(0));
    }
}

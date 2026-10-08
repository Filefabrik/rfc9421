<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Tools\Signer;

use Filefabrik\Rfc9421\Signer\SignatureLine;
use Filefabrik\Rfc9421\Signer\SignatureLines;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Filefabrik\Rfc9421\Signer\SignatureLines::class)]
class SignatureLinesTest extends TestCase
{

    public function testToArray(): void
    {
        $sl = new SignatureLines();
        $sl->add(new SignatureLine('foo', 'bar'));
        self::assertEquals([['foo', 'bar']], $sl->toArray());
    }

    public function testGetLineNumber(): void
    {
        $sl = new SignatureLines();
        $sl->add(new SignatureLine('foo', 'bar'));
        self::assertEquals(new SignatureLine('foo', 'bar'), $sl->getLineNumber(0));
    }

    public function testAdd(): void
    {
        $sl = new SignatureLines();
        $sl->add(new SignatureLine('foo', 'bar'));
        self::assertEquals(new SignatureLine('foo', 'bar'), $sl->getLineNumber(0));
    }

    public function testAddEntry(): void
    {
        $sl = new SignatureLines();
        $sl->addEntry('foo', 'bar');
        self::assertEquals(new SignatureLine('foo', 'bar'), $sl->getLineNumber(0));
    }
}

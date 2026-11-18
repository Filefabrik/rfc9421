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

use Filefabrik\Rfc9421\Signer\Signatures\Line;
use Filefabrik\Rfc9421\Signer\Signatures\WriteLines;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WriteLines::class)]
final class WriteLinesTest extends TestCase
{
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
        self::assertEquals(new Line('foo', 'bar'), $sl->getLines()[0]);
    }

    public function testAdd(): void
    {
        $sl = new WriteLines();
        $sl->add(new Line('foo', 'bar'));
        self::assertEquals(new Line('foo', 'bar'), $sl->getLines()[0]);
    }

    public function testAddEntry(): void
    {
        $sl = new WriteLines();
        $sl->addEntry('foo', 'bar');
        self::assertEquals(new Line('foo', 'bar'), $sl->getLines()[0]);
    }

    public function testKeys(): void
    {
        $sl = new WriteLines();
        $sl->add(new Line('foo', 'bar'))
           ->add(new Line('foo2', 'bar2'))
        ;
        self::assertEquals(['foo', 'foo2'], $sl->keys());
    }

    public function testValues(): void
    {
        $sl = new WriteLines();
        $sl->add(new Line('foo', 'bar'))
           ->add(new Line('foo2', 'bar2'))
        ;
        self::assertEquals(['bar', 'bar2'], $sl->values());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testFromString(): void
    {
        $lines   = new WriteLines()
            ->fromSignatureLine('"content-digest": sha-256=:qvoLOe+II0NxvDyMNg80z+xDv6aD9IkT9w8a340Q1Oo=:')
        ;
        $setLine = $lines->getLines()[0];
        self::assertSame(
            ['"content-digest"', 'sha-256=:qvoLOe+II0NxvDyMNg80z+xDv6aD9IkT9w8a340Q1Oo=:'],
            [$setLine->signatureComponent, $setLine->valueToSign],
        );
    }
}

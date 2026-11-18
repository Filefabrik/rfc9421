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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Line::class)]
final class LineTest extends TestCase
{
    public function test__construct(): void
    {
        $i = new Line('one', 'val');
        self::assertEquals('one', $i->signatureComponent);
        self::assertEquals('val', $i->valueToSign);
    }

    /**
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testGetItem(): void
    {
        $i = new Line('one', 'val');
        self::assertEquals('one', $i->signatureComponent);

        self::assertSame(
            '"one"',
            $i->getComponentItemDto()
              ->getHttpValue(),
        );
    }

    public function testToLineString(): void
    {
        $i = new Line('"one"', 'val');
        self::assertEquals('"one"', $i->signatureComponent);

        self::assertSame(
            '"one": val',
            $i->toLineString(),
        );
    }
}

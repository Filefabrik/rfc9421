<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * todo insert correct copyright
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
}

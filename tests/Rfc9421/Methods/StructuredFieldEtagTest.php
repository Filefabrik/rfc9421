<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Methods;

use Filefabrik\Rfc9421\Methods\StructuredFieldEtag;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StructuredFieldEtag::class)]
final class StructuredFieldEtagTest extends TestCase
{
    public function testHandle(): void
    {
        $res = StructuredFieldEtag::handle('W/"abcdef", "ghijkl", *');
        self::assertSame('"abcdef"; w, "ghijkl", *', $res);

        $res = StructuredFieldEtag::handle(' my value ');
        self::assertSame('my value', $res);
    }
}

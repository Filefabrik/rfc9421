<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Signature;

use Filefabrik\Rfc9421\Enums\InitialContentsEnum;
use Filefabrik\Rfc9421\Signature\InitialContentsParameters;
use Filefabrik\Rfc9421\Signature\RenderInitialContents;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RenderInitialContents::class)]
final class RenderInitialContentsTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function testToArray(): void
    {
        $ip = new InitialContentsParameters();
        $ip->setParameter(InitialContentsEnum::key, 'key.va')->setParameter(InitialContentsEnum::bs);
        $parts = new RenderInitialContents($ip)->toArray();

        self::assertSame(
            ['key="key.va"','bs'],
            $parts,
        );


    }
}

<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Configuration;

use Filefabrik\Rfc9421\Configuration\GenerateContentDigestTrait;
use Filefabrik\Rfc9421\SigningConfiguration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SigningConfiguration::class)]
final class GenerateContentDigestTraitTest extends TestCase
{
    public function testMustCreateContentDigest(): void
    {
        $instance = new class () {
            use GenerateContentDigestTrait;
        };

        //$instance = new GenerateContentDigestFaked();
        self::assertFalse($instance->isMustGenerateContentDigest());
        $instance->mustCreateContentDigest();
        self::assertTrue($instance->isMustGenerateContentDigest());
    }

}

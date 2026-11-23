<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Enums;

use Filefabrik\Rfc9421\Enums\HeaderKeysEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HeaderKeysEnum::class)]
final class HeaderKeysEnumTest extends TestCase
{
    public function testCount(): void
    {
        self::assertCount(1, HeaderKeysEnum::cases());
    }

    public function testFieldName(): void
    {
        self::assertSame('content-digest', HeaderKeysEnum::content_digest->fieldName());

    }
}

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

use Filefabrik\Rfc9421\Enums\InternalHeaderKeyEnums;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InternalHeaderKeyEnums::class)]
final class InternalHeaderKeyEnumsTest extends TestCase
{
    public function testCount(): void
    {
        self::assertCount(2, InternalHeaderKeyEnums::cases());
    }

    public function testFieldName(): void
    {
        self::assertSame('signature', InternalHeaderKeyEnums::signature->fieldName());
        self::assertSame('signature-input', InternalHeaderKeyEnums::signature_input->fieldName());
    }
}

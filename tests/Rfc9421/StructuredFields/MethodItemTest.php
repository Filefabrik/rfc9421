<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\StructuredFields;

use Filefabrik\Rfc9421\StructuredFields\MethodItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;

#[CoversClass(MethodItem::class)]
final class MethodItemTest extends TestCase
{
    public function test__construct(): void
    {
        $closure    = function (MessageInterface $message): void {
        };
        $methodItem = new MethodItem('methodName', $closure);
        self::assertSame('methodName', $methodItem->method);
    }
}

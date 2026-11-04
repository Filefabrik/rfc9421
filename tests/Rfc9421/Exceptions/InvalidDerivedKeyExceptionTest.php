<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Exceptions;

use Filefabrik\Rfc9421\Exceptions\InvalidDerivedKeyException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidDerivedKeyException::class)]
final class InvalidDerivedKeyExceptionTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidDerivedKeyException
     */
    public function testInvalidDerivedKey(): void
    {
        $this->expectExceptionObject(new InvalidDerivedKeyException('Derived component name must start with "@"'));
        throw InvalidDerivedKeyException::invalidDerivedKey();
    }
}

<?php

declare(strict_types=1);

namespace Filefabrik\HsapTest\Exceptions;

use Filefabrik\Hsap\Exceptions\InvalidDerivedKeyException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidDerivedKeyException::class)]
final class InvalidDerivedKeyExceptionTest extends TestCase
{
    public function testInvalidDerivedKey(): void
    {
        $this->expectExceptionObject(new InvalidDerivedKeyException('Derived component name must start with "@"'));
        throw InvalidDerivedKeyException::invalidDerivedKey();
    }
}

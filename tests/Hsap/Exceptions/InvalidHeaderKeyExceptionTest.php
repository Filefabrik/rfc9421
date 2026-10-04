<?php

declare(strict_types=1);

namespace Filefabrik\HsapTest\Exceptions;

use Filefabrik\Hsap\Exceptions\InvalidDerivedKeyException;
use Filefabrik\Hsap\Exceptions\InvalidHeaderKeyException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Filefabrik\Hsap\Exceptions\InvalidHeaderKeyException::class)]
final class InvalidHeaderKeyExceptionTest extends TestCase
{
    public function testHeaderNotExists(): void
    {
        $this->expectExceptionObject(new InvalidHeaderKeyException('The header "test-header-name" does not exist in the message. Note:headers can only be a part of the signing process if they are existing. $message->withHeader("test-header-name", \'your value\')'));
        throw InvalidHeaderKeyException::headerNotExists('test-header-name');
    }
}

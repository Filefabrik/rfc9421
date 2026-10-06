<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Exceptions;

use Filefabrik\Rfc9421\Exceptions\SignatureException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SignatureException::class)]
final class SignatureExceptionTest extends TestCase
{
    public function testSignatureEnumNotFound(): void
    {
        $this->expectExceptionObject(new SignatureException('The signature enum "-bullshit-algo-" for signing is not supported.'));
        throw SignatureException::signatureEnumNotFound('-bullshit-algo-');
    }
}

<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Exceptions;

use Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(VerifyHeaderSignatureException::class)]
final class VerifyHeaderSignatureExceptionTest extends TestCase
{
    public function testInvalidSignatureLine(): void
    {
        $this->expectExceptionObject(new VerifyHeaderSignatureException('Invalid signature line "playcs"'));
        throw VerifyHeaderSignatureException::invalidSignatureLine('playcs');
    }

    public function testKeynameNotExtracted(): void
    {
        $this->expectExceptionObject(
            new VerifyHeaderSignatureException('Error during extracting the signatureKeyName'),
        );
        throw VerifyHeaderSignatureException::keynameNotExtracted();
    }

    public function testMissingSignatureInput(): void
    {
        $this->expectExceptionObject(
            new VerifyHeaderSignatureException('Missing signature-input'),
        );
        throw VerifyHeaderSignatureException::missingSignatureInput();
    }

    public function testMissingSignature(): void
    {
        $this->expectExceptionObject(
            new VerifyHeaderSignatureException('Missing signature'),
        );
        throw VerifyHeaderSignatureException::missingSignature();
    }

    public function testInvalidSignatureKeyName(): void
    {

      $this->expectExceptionObject(
          new VerifyHeaderSignatureException('Error during extracting the signatureKeyName from signature-input. Expects the keyname: "testSig=" in header: "ugly="'),
      );
        throw VerifyHeaderSignatureException::invalidSignatureKeyName('testSig=', 'ugly=');
    }
}

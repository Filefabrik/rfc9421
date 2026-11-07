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

use Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(VerifyHeaderSignatureException::class)]
final class VerifyHeaderSignatureExceptionTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     */
    public function testInvalidSignatureLine(): void
    {
        $this->expectExceptionObject(new VerifyHeaderSignatureException('Invalid signature line "playcs"'));
        throw VerifyHeaderSignatureException::invalidSignatureLine('playcs');
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     */
    public function testKeynameNotExtracted(): void
    {
        $this->expectExceptionObject(
            new VerifyHeaderSignatureException('Error during extracting the signatureKeyName'),
        );
        throw VerifyHeaderSignatureException::keynameNotExtracted();
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     */
    public function testMissingSignatureInput(): void
    {
        $this->expectExceptionObject(
            new VerifyHeaderSignatureException('Missing signature-input'),
        );
        throw VerifyHeaderSignatureException::missingSignatureInput();
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     */
    public function testMissingSignature(): void
    {
        $this->expectExceptionObject(
            new VerifyHeaderSignatureException('Missing signature'),
        );
        throw VerifyHeaderSignatureException::missingSignature();
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     */
    public function testInvalidSignatureKeyName(): void
    {
        $this->expectExceptionObject(
            new VerifyHeaderSignatureException(
                'Error during extracting the signatureKeyName from signature-input. Expects the keyname: "testSig=" in header: "ugly="',
            ),
        );
        throw VerifyHeaderSignatureException::invalidSignatureKeyName('testSig=', 'ugly=');
    }
}

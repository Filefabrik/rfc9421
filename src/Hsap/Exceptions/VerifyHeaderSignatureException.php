<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Exceptions;

use Exception;

/**
 * during the VerificationHeaderSignature
 */
final class VerifyHeaderSignatureException extends Exception
{
    public static function missingSignature(): self
    {
        return new self('Missing signature');
    }

    public static function missingSignatureInput(): self
    {
        return new self('Missing signature-input');
    }

    public static function keynameNotExtracted(): self
    {
        return new self('Error during extracting the signatureKeyName');
    }

    public static function invalidSignatureKeyName(string $keyname, string $header): self
    {
        return new self(
            sprintf(
                'Error during extracting the signatureKeyName from signature-input. Expects the keyname: "%s" in header: "%s"',
                $keyname,
                $header,
            ),
        );
    }

    public static function invalidSignatureLine(string $signatureLine): self
    {
        return new self(sprintf('Invalid signature line "%s"', $signatureLine));
    }
}

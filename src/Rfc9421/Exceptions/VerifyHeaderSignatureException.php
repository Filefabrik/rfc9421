<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Exceptions;

use Filefabrik\Rfc9421\Enums\InternalHeaderKeyEnums;

/**
 * during the VerificationHeaderSignature
 */
final class VerifyHeaderSignatureException extends AbstractRfc9421Exception
{
    public static function missingSignature(): self
    {
        return new self('Missing signature');
    }

    public static function missingSignatureInput(): self
    {
        return new self(sprintf('Missing %s', InternalHeaderKeyEnums::signature_input->value));
    }

    public static function keynameNotExtracted(): self
    {
        return new self('Error during extracting the signatureKeyName');
    }

    public static function invalidSignatureKeyName(string $keyname, string $header): self
    {
        return new self(
            sprintf(
                'Error during extracting the signatureKeyName from %s. Expects the keyname: "%s" in header: "%s"',
                InternalHeaderKeyEnums::signature_input->value,
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

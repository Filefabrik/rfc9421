<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Exceptions;

final class SignatureException extends AbstractRfc9421Exception
{
    public static function signatureEnumNotFound(string|null $alg): self
    {
        return new self(sprintf('The signature enum "%s" for signing is not supported.', $alg));
    }

    public static function wrongFormat(): self
    {
        return new self('The signature algorithm must be a string|SigningEnumInterface.');
    }

    public static function notSet(): self
    {
        return new self('Signature algorithm must be set.');
    }
}

<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Exceptions;

use Exception;

final class SignatureException extends Exception
{
    public static function signatureEnumNotFound(string|null $alg): self
    {
        return new self(sprintf('The signature enum "%s" for signing is not supported.', $alg));
    }
}

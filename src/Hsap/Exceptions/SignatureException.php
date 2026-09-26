<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Exceptions;

use Exception;

final class SignatureException extends Exception
{
    public static function signatureEnumNotFound(string $alg): self
    {
        return new self(sprintf('The signature enum "%s" for signing is not supported.', $alg));
    }
}

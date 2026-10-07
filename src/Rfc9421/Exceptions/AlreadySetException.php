<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Exceptions;

use Exception;

final class AlreadySetException extends Exception
{
    public static function inputSignature(null|int|string $var = null): self
    {
        return new self(
            sprintf('Signature-Input parameter "%s" was set before and can not be set again.', $var),
        );
    }
}

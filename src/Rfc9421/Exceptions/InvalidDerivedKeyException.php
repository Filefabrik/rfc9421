<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Exceptions;

use Exception;

final class InvalidDerivedKeyException extends Exception
{
    public static function invalidDerivedKey(): self
    {
        return new self('Derived component name must start with "@"');
    }
}

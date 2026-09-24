<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Exceptions;

use Exception;

final class InvalidDerivedKeyException extends Exception
{
    public static function invalidDerivedKey(): self
    {
        return new self('Derived component name must start with "@"');
    }
}

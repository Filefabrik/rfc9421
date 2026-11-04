<?php
/*
 * Copyright (c) 2026.  rt@filefabrik.com
 * proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Exceptions;

final class InvalidDerivedKeyException extends AbstractRfc9421Exception
{
    public static function invalidDerivedKey(): self
    {
        return new self('Derived component name must start with "@"');
    }
}

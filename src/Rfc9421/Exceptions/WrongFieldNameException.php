<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Exceptions;

final class WrongFieldNameException extends AbstractRfc9421Exception
{
    public static function wrongFieldName(): self
    {
        return new self('Field name must be a string');
    }
}

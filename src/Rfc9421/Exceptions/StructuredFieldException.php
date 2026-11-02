<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Exceptions;

final class StructuredFieldException extends AbstractRfc9421Exception
{
    public static function notMapped(string $key): self
    {
        return new self(sprintf('The field "%s" is not mapped into StructuredFields.', $key));
    }
}

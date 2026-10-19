<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Exceptions;

final class MethodException extends AbstractRfc9421Exception
{
    public static function notCallable(string $className, string $methodName): self
    {
        return new self(sprintf('Missing method "%s" for MessageInterface "%s"', $methodName, $className));
    }
}

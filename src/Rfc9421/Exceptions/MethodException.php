<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Exceptions;

final class MethodException extends AbstractRfc9421Exception
{
    public static function notCallable(string $className, string $methodName): self
    {
        return new self(sprintf('Missing method "%s" for MessageInterface "%s"', $methodName, $className));
    }

    public static function closureCallError(string $message = '-no message-'): self
    {
        return new self(sprintf('Closure Call Error with Message "%s"', $message));
    }

    public static function reflectionCallError(string $message = '-no message-'): self
    {
        return new self(sprintf('Closure Call Reflection Error with Message "%s"', $message));
    }

    public static function notFoundInMethodsMapper(string $methodName): self
    {
        return new self(sprintf('There is no registered Method "%s" to process the value.', $methodName));
    }
}

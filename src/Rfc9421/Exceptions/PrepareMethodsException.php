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

use Throwable;

final class PrepareMethodsException extends AbstractRfc9421Exception
{
    public static function methodNotRegistered(string $key): self
    {
        return new self(sprintf('Method "%s" not registered.', $key));
    }

    public static function errorFromCalling(string $key, Throwable $exception): self
    {
        return new self(
            sprintf('Error during calling the method with key: "%s": %s', $key, $exception->getMessage()),
            $exception->getCode(),
            $exception,
        );
    }
}

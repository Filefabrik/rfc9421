<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Exceptions;

use Exception;

/**
 * @codeCoverageIgnore
 */
final class InvalidHeaderKeyException extends Exception
{
    public static function headerNotExists(string $headerName): self
    {
        return new self(
            sprintf(
                'The header "%s" does not exist in the message. Note:headers can only be a part of the signing process if they are existing. $message->withHeader("%s", \'your value\')',
                $headerName,
                $headerName,
            ),
        );
    }
}

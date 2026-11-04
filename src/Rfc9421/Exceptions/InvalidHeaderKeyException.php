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

/**
 * @codeCoverageIgnore
 */
final class InvalidHeaderKeyException extends AbstractRfc9421Exception
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

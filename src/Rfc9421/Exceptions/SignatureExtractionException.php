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

final class SignatureExtractionException extends AbstractRfc9421Exception
{
    public static function notProceeded(): self
    {
        /* @noinspection PhpFullyQualifiedNameUsageInspection */
        return new self(
            sprintf(
                'Signature is not proceeded. Call before %s::extract',
                \Filefabrik\Rfc9421\Verify\HeaderSignatureInput::class,
            ),
        );
    }

    public static function invalidLine(string $line): self
    {
        return new self(sprintf('Invalid signature line:%s', $line));
    }

    public static function invalidParameter(string $parameter): self
    {
        return new self(sprintf('Invalid signature parameter:%s', $parameter));
    }
}

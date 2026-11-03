<?php

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
                \Filefabrik\Rfc9421\Verify\HeaderSignatureInput::class
            ),
        );
    }
}

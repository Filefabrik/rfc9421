<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Exceptions;

final class SignatureExtractionException extends AbstractRfc9421Exception
{
    public static function notProceeded(): self
    {
        return new self(
            'Signature is not proceeded. Call before \Filefabrik\Rfc9421\Verify\HeaderSignatureInput::extract',
        );
    }
}

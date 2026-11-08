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

final class ProcessingException extends AbstractRfc9421Exception
{
    public static function SignedHeaderLines(): self
    {
        return new self('VerifyRequest->getSignedHeaderLines() can be called only after VerifyRequest->verifying()');
    }

    public static function signingParametersString(): self
    {
        return new self('Signing parameter string was not set before. First you have to call an sign or verify method.');
    }

    public static function signingLines(): self
    {
        return new self('Signing\Lines not set.');
    }
}

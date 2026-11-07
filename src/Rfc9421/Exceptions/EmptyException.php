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

final class EmptyException extends AbstractRfc9421Exception
{
    public static function privatePemIsEmpty(): self
    {
        return new self('Private pem is empty.');
    }

    public static function publicPemIsEmpty(): self
    {
        return new self('Public pem is empty.');
    }

}

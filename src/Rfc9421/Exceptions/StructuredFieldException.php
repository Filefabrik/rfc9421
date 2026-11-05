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

use Filefabrik\Rfc9421\Enums\InitialContentsEnum;

final class StructuredFieldException extends AbstractRfc9421Exception
{
    public static function notMapped(string $key): self
    {
        return new self(sprintf('The field "%s" is not mapped into StructuredFields.', $key));
    }

    public static function missingKeyOrName(string $key, InitialContentsEnum $initialContentsEnum): self
    {
        return new self(
            sprintf(
                'For the Entry-Field "%s" there was no ;%s="#missing#" key given.',
                $key,
                $initialContentsEnum->name,
            ),
        );
    }
}

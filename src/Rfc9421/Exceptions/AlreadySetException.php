<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Exceptions;

use Filefabrik\Rfc9421\Enums\DerivedComponentEnum;
use Filefabrik\Rfc9421\Enums\HeaderKeysEnum;

final class AlreadySetException extends AbstractRfc9421Exception
{
    public static function inputSignature(null|int|string $var = null): self
    {
        return new self(
            sprintf(
                '%s parameter "%s" was set before and can not be set again.',
                HeaderKeysEnum::signature_input->value,
                $var,
            ),
        );
    }

    public static function ComponentExists(string|DerivedComponentEnum $componentName): self
    {
        $v = $componentName instanceof DerivedComponentEnum ? sprintf('"%s"', $componentName->value) : $componentName;

        return new self(
            sprintf(
                'The Component %s already exists.',
                $v,
            ),
        );
    }
}

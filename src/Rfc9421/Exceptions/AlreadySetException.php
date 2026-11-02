<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Exceptions;

use Filefabrik\Rfc9421\Signature\DerivedComponentEnum;

final class AlreadySetException extends AbstractRfc9421Exception
{
    public static function inputSignature(null|int|string $var = null): self
    {
        return new self(
            sprintf(
                'Signature-Input parameter "%s" was set before and can not be set again.',
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

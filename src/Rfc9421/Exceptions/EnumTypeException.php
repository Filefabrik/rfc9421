<?php
/*
 * Copyright (c) 2026.  rt@filefabrik.com
 * proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Exceptions;

final class EnumTypeException extends AbstractRfc9421Exception
{
    /**
     * @param class-string $enum
     *
     */
    public static function TypeError(string $type, string $enum): self
    {
        return new self(sprintf('"%s" is not a valid enumeration of "%s" Enum.', $type, $enum));
    }
}

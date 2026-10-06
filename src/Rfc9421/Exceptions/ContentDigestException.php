<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Exceptions;

use Exception;

final class ContentDigestException extends Exception
{
    public static function wrongInputFormat(): self
    {
        return new self('Wrong content digest format');
    }

    public static function bodyTempered(): self
    {
        return new self('Body was tempered');
    }

    public static function hashAlgoNotSupported(string $alg): self
    {
        return new self(sprintf('Hash algorithm "%s" for the content-digest not supported.', $alg));
    }
}

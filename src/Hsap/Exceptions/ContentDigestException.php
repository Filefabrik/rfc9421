<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Exceptions;

final class ContentDigestException extends \Exception
{
    public static function wrongInputFormat(): self
    {
        return new self('Wrong content digest format');
    }

    public static function bodyTempered(): self
    {
        return new self('Body was tempered');
    }
}

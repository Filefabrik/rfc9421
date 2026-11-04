<?php
/*
 * Copyright (c) 2026.  rt@filefabrik.com
 * proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Exceptions;

use Filefabrik\Rfc9421\Enums\HeaderKeysEnum;

final class ContentDigestException extends AbstractRfc9421Exception
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
        return new self(sprintf('Hash algorithm "%s" for the %s not supported.', $alg, HeaderKeysEnum::content_digest->value));
    }

    public static function canNotHashWithoutAlgorithm(): self
    {
        return new self(sprintf('%s header is set but the ContentDigestHashAlgo was not configuration.', HeaderKeysEnum::content_digest->value));
    }
}

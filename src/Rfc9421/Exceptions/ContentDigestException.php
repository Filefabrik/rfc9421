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
        return new self(
            sprintf('Hash algorithm "%s" for the %s not supported.', $alg, HeaderKeysEnum::content_digest->value),
        );
    }

    public static function canNotHashWithoutAlgorithm(): self
    {
        return new self(
            sprintf(
                '%s header is set but the ContentDigestHashAlgo was not configuration.',
                HeaderKeysEnum::content_digest->value,
            ),
        );
    }

    public static function headerContentDigestAlreadySet(): self
    {
        return new self(
            sprintf(
                'The HTTP Message Header %s exists. So the %s can not be written again',
                HeaderKeysEnum::content_digest->value,
                HeaderKeysEnum::content_digest->value,
            ),
        );
    }
}

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

/**
 * Query Params has there on Exception Class
 */
final class QueryParamsException extends AbstractRfc9421Exception
{
    public static function keyException(string $key): self
    {
        return new self(
            sprintf(
                'Decoding and Encoding of "%s" does not match with the given key. So something is wrong with the QueryParams key',
                $key,
            ),
        );
    }
    public static function valueException(string $value): self
    {
        return new self(
            sprintf(
                'Decoding and Encoding of "%s" does not match with the given value. So something is wrong with the QueryParams value',
                $value,
            ),
        );
    }

    public static function emptyQueryString(): self
    {
        return new self('Query string is empty');
    }
}

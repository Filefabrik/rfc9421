<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Bakame;

use Bakame\Http\StructuredFields\StructuredFieldError;
use Exception;
use Filefabrik\Rfc9421\Exceptions\StructuredFieldException;

/**
 * Bakame Wrapper for Errors
 */
final class Dictionary
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     */
    public static function fromHttpValue(string $headerValue): \Bakame\Http\StructuredFields\Dictionary
    {
        // @infection-ignore Catch_
        try {
            return \Bakame\Http\StructuredFields\Dictionary::fromHttpValue($headerValue);
        } catch (StructuredFieldError|Exception $e) {
            throw StructuredFieldException::structuredFieldExceptionBakame($e);
        }
    }
}

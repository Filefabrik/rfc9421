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

use Exception;


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
        try {
            return \Bakame\Http\StructuredFields\Dictionary::fromHttpValue($headerValue);
        } catch (\Bakame\Http\StructuredFields\StructuredFieldError|Exception $e) {
            throw new \Filefabrik\Rfc9421\Exceptions\StructuredFieldException($e->getMessage(), $e->getCode(), $e);
        }
    }
}

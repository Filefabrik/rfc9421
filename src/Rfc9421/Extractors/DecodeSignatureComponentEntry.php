<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Extractors;

/**
 *  Handle a signature line
 */
final class DecodeSignatureComponentEntry
{
    /**
     * Extracting a single line with signature-input-format
     *
     * @see \Filefabrik\Rfc9421Test\Extractors\DecodeSignatureComponentEntryTest::testExtract()
     */
    public static function extract(string $line): ?array
    {
        // must be 4
        preg_match('#"(.*?)"(.*?): (.*?)$#', $line, $matches);

        return $matches;
    }
}

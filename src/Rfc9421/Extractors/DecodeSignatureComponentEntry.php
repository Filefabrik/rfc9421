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

use Filefabrik\Rfc9421\Dto\ComponentItemDto;
use Filefabrik\Rfc9421\Exceptions\SignatureExtractionException;
use Filefabrik\Rfc9421\Signature\ComponentName;
use Filefabrik\Rfc9421\Signer\Signatures\Line;
use Filefabrik\Rfc9421\StructuredFields\Entry;
use Filefabrik\Rfc9421\Support\UnquoteHelper;

/**
 * Helper to Handle a signature line
 */
final class DecodeSignatureComponentEntry
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public static function toLine(string $line): Line
    {
        $extracted = self::extractOrThrow($line);

        $componentItemDto = self::toComponentDto($line);

        $key = $componentItemDto->getHttpValue();

        return new Line(signatureComponent: $key, valueToSign: self::concatKeyValue($key, $extracted[3]));
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @return array{0:string,1:string,2:string,3:string}
     */
    public static function extractOrThrow(string $line): array
    {
        $extracted = self::extract($line);

        return 4 === count($extracted) ? $extracted : throw SignatureExtractionException::invalidLine($line);
    }

    /**
     * Extracting a single line with signature-input-format
     *
     * @return array{0:string,1:string,2:string,3:string}|array{}
     * @see \Filefabrik\Rfc9421Test\Extractors\DecodeSignatureComponentEntryTest::testExtract()
     */
    public static function extract(string $line): array
    {
        // must be 4
        preg_match('#"(.*?)"(.*?): (.*?)$#', $line, $matches);

        // empty array is error
        return (array)$matches;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public static function toComponentDto(string $line): ComponentItemDto
    {
        $extracted = self::extractOrThrow($line);
        $quoted    = UnquoteHelper::quote($extracted[1]);
        $string    = $quoted.$extracted[2];

        return ComponentName::fromString($string);
    }

    public static function concatKeyValue(string $key, string $value): string
    {
        return $key.Entry::colonWithSpace.$value;
    }
}

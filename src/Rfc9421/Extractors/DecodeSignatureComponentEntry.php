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

use Filefabrik\Rfc9421\Exceptions\SignatureExtractionException;
use Filefabrik\Rfc9421\Signature\ComponentBuilder;
use Filefabrik\Rfc9421\Signature\ComponentExtractor;
use Filefabrik\Rfc9421\Signer\Signatures\Line;
use Filefabrik\Rfc9421\Support\UnquoteHelper;

/**
 * Helper to Handle a signature line
 *
 * @example splits "content-digest": sha-256=:qvoLOe+II0NxvDyMNg80z+xDv6aD9IkT9w8a340Q1Oo=: into the ComponentBuilder and the Value
 */
final class DecodeSignatureComponentEntry
{
    /**
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public static function toLine(string $line): Line
    {
        $builderValue = self::extractToBuilderAndValue($line);
        $component    = $builderValue['builder']
            ->toComponentItemDto()
            ->getComponent()
        ;

        return new Line(
            signatureComponent: $component,
            valueToSign       : $builderValue['value'],
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @return array{builder:ComponentBuilder,value:string}
     */
    private static function extractToBuilderAndValue(string $line): array
    {
        $extracted = self::extractOrThrow($line);

        $quoted = UnquoteHelper::quote($extracted[1]);
        $string = $quoted.$extracted[2];

        return ['builder' => ComponentExtractor::extractToBuilder($string), 'value' => $extracted[3]];
    }

    /**
     * todo make private
     *
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
     * todo make private
     *
     * @return array{0:string,1:string,2:string,3:string}|array{}
     * @see \Filefabrik\Rfc9421Test\Extractors\DecodeSignatureComponentEntryTest::testExtract()
     */
    public static function extract(string $line): array
    {
        // must be 4
        // todo use the ComponentName Extractor
        preg_match('#"(.*?)"(.*?): (.*?)$#', $line, $matches);

        // empty array is error
        return (array)$matches;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public static function toBuilder(string $line): ComponentBuilder
    {
        return self::extractToBuilderAndValue($line)['builder'];
    }

}

<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signature;

use Filefabrik\Rfc9421\Dto\ComponentItemDto;
use Filefabrik\Rfc9421\Enums\DerivedComponentEnum;
use Filefabrik\Rfc9421\Enums\InitialContentsEnum;
use Filefabrik\Rfc9421\Exceptions\SignatureExtractionException;
use Filefabrik\Rfc9421\Support\UnquoteHelper;

/**
 * This method is only used to prepare internal setting during the setHeader
 * Single component with InitialContentsEnum or without InitialContentsEnum
 * or with structured fields
 *
 */
final class ComponentExtractor
{
    /**
     * Can be a full string which has to be extracted
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public static function extractToComponentItemDto(string|DerivedComponentEnum $component): ComponentItemDto
    {
        $componentBuilder = $component instanceof DerivedComponentEnum ?
            new ComponentBuilder($component) :
            self::extractToBuilder($component);

        return $componentBuilder->toComponentItemDto();
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public static function extractToBuilder(string|DerivedComponentEnum $component): ComponentBuilder
    {
        return $component instanceof DerivedComponentEnum ?
            new ComponentBuilder($component) :
            self::extractor($component);
    }

    /**
     * new approach
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    private static function extractor(string $segment): ComponentBuilder
    {
        $pattern = '^"(.*?)"(.*?)$';
        preg_match('#'.$pattern.'#', $segment, $matches);

        // only the fieldName without quotas
        // todo with more testing against the fieldName
        if (0 === count($matches)) {
            if (str_contains($segment, '"') || str_contains($segment, ';')) {
                throw  SignatureExtractionException::invalidLine($segment);
            }

            // this is the fieldName
            return new ComponentBuilder($segment);
        }

        // this is the fieldName
        $componentBuilder = new ComponentBuilder($matches[1]);
        if ('' !== $matches[2]) {
            $componentBuilder = self::handleSemicolons($matches[2], $componentBuilder);
        }

        return $componentBuilder;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    private static function handleSemicolons(string $subSegment, ComponentBuilder $componentBuilder): ComponentBuilder
    {
        if (!str_starts_with($subSegment, ComponentBuilder::semicolon)) {
            throw SignatureExtractionException::invalidParameter($subSegment);
        }

        $lTrimmed = ltrim($subSegment, ComponentBuilder::semicolon);
        // todo more intensive testing
        foreach (explode(ComponentBuilder::semicolon, $lTrimmed) as $item) {
            // check we have a key="" or fieldName=""
            $kvPair       = explode(ComponentBuilder::equals, $item);
            $cnt          = count($kvPair);
            $contentsEnum = InitialContentsEnum::isOwnTypeOrThrow($kvPair[0]);
            if (1 === $cnt) {
                // detect and set flagless
                $componentBuilder->setValueless($contentsEnum);
            } elseif (2 === $cnt) {
                // detect and set with value
                $unquoted = UnquoteHelper::unquote($kvPair[1]);
                $componentBuilder->setWithValue($contentsEnum, $unquoted);
            } else {
                // @codeCoverageIgnoreStart
                throw SignatureExtractionException::invalidParameter($subSegment);
                // @codeCoverageIgnoreEnd
            }
        }

        return $componentBuilder;
    }
}

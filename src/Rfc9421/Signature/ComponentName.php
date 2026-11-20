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
 * @deprecated use ComponentBuilder
 */
final class ComponentName
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public static function fromString(string|DerivedComponentEnum $name): ComponentItemDto
    {
        $componentBuilder = $name instanceof DerivedComponentEnum ?
            new ComponentBuilder($name) :
            self::extractor($name);

        return $componentBuilder->toComponentItemDto();
    }

    /**
     * new approach
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public static function extractor(string $segment): ComponentBuilder
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
        if ($matches[2]) {
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

        $ltrimmed = ltrim($subSegment, ComponentBuilder::semicolon);
        // todo more intensive testing
        foreach (explode(ComponentBuilder::semicolon, $ltrimmed) as $item) {
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

    /**
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public static function withSf(string|DerivedComponentEnum $name, bool $asReq = false): ComponentItemDto
    {
        return new ComponentBuilder($name)->withSf()
                                          ->setReq($asReq)
                                          ->toComponentItemDto()
        ;
    }

    /**
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public static function withBs(string|DerivedComponentEnum $name, bool $asReq = false): ComponentItemDto
    {
        return new ComponentBuilder($name)->withBs()
                                          ->setReq($asReq)
                                          ->toComponentItemDto()
        ;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @example with "COMPONENT-NAME";fieldName="VALUE"
     * @example with "COMPONENT-NAME";req;fieldName="VALUE"
     */
    public static function withName(
        string|DerivedComponentEnum $name,
        string $nameValue,
        bool $asReq = false,
    ): ComponentItemDto {
        return new ComponentBuilder($name)->withName($nameValue)
                                          ->setReq($asReq)
                                          ->toComponentItemDto()
        ;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @example with "COMPONENT-NAME";req;key="VALUE"
     * @example with "COMPONENT-NAME";key="VALUE"
     */
    public static function withKey(
        string|DerivedComponentEnum $name,
        string $keyValue,
        bool $asReq = false,
    ): ComponentItemDto {
        return new ComponentBuilder($name)->withKey($keyValue)
                                          ->setReq($asReq)
                                          ->toComponentItemDto()
        ;
    }

    /**
     * Only the component key perhaps the ';req'
     *
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public static function withComponent(string|DerivedComponentEnum $name, bool $asReq = false): ComponentItemDto
    {
        return ComponentItemDto::fromComponentBuilder(new ComponentBuilder($name)->setReq($asReq));
    }
}
